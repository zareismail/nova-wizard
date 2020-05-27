<template>
  <loading-view :loading="loading">
    <custom-create-header class="mb-3" :resource-name="resourceName" />
    <wizard-form
      :navigation="$route.navigable"
      :resource-name="resourceName"
      :resource-id="resourceId"
      :via-resource="viaResource"
      :via-resource-id="viaResourceId"
      :via-relationship="viaRelationship"
      :panels="panelsWithFields"
      :next-handler="handleNext"
      :previous-handler="handlePrevious"
      :submit-handler="handleSubmit"
      :submit="__('Create :resource', { resource: singularName })"
      :submit-and-stay="__('Create & Add Another')"
      :validation-errors="validationErrors"
      @cancelled="handleCancelled"
    />
  </loading-view>
</template>

<script>
import {
  mapProps,
  Errors,
  InteractsWithResourceInformation,
} from 'laravel-nova'
import HandlesSteps from './HandlesSteps'

export default {
  mixins: [InteractsWithResourceInformation, HandlesSteps],

  props: {
    mode: {
      type: String,
      default: 'form',
      validator: val => ['modal', 'form'].includes(val),
    },

    ...mapProps([
      'resourceName',
      'resourceId',
      'viaResource',
      'viaResourceId',
      'viaRelationship',
    ]),
  },

  data: () => ({
    relationResponse: null,
    loading: true,
    validationErrors: new Errors(),
  }),

  async created() {
    if (Nova.missingResource(this.resourceName))
      return this.$router.push({ name: '404' })

    this.initializeStep()

    // If this create is via a relation index, then let's grab the field
    // and use the label for that as the one we use for the title and buttons
    if (this.isRelation) {
      const { data } = await Nova.request().get(
        '/nova-api/' + this.viaResource + '/field/' + this.viaRelationship,
        {
          params: {
            resourceName: this.resourceName,
            viaResource: this.viaResource,
            viaResourceId: this.viaResourceId,
            viaRelationship: this.viaRelationship,
          },
        }
      )
      this.relationResponse = data

      if (this.isHasOneRelationship && this.alreadyFilled) {
        Nova.error(this.__('The HasOne relationship has already filled.'))

        this.$router.push({
          name: 'detail',
          params: {
            resourceId: this.viaResourceId,
            resourceName: this.viaResource,
          },
        })
      }
    }

    this.getFields()
  },


  methods: {
    /**
     * Get the available fields for the resource.
     */
    async getFields() {
      this.panels = []
      this.fields = []

      const {
        data: { panels, fields },
      } = await Nova.request().get(
        `/nova-api/${this.resourceName}/step/${this.step}/creation-fields`,
        {
          params: {
            editing: true,
            editMode: 'create',
            resourceId: this.resourceId,
            viaResource: this.viaResource,
            viaResourceId: this.viaResourceId,
            viaRelationship: this.viaRelationship,
          },
        }
      )

      this.panels = panels
      this.fields = fields
      this.loading = false
    },

    handleCancelled() {
      if (this.mode == 'form') {
        return this.$router.back()
      }

      return this.$emit('cancelled-create')
    },

    async handleNext(formData) {
      try {
        const {
          data: { redirect, id },
        } = await this.checkpointRequest(formData, this.currentPanel.checkpoint !== true)

        if(this.currentPanel.checkpoint !== true)
          Nova.success(
            this.__('The session was saved!')
          )
        else
          Nova.success(
            this.__('The :resource was created!', {
              resource: this.resourceInformation.singularLabel.toLowerCase(),
            })
          )

        this.validationErrors = new Errors()
        this.resourceId = id;
        this.step++

        await this.getFields()
      } catch (error) {
        if (error.response.status == 422) {
          this.validationErrors = new Errors(error.response.data.errors)
          Nova.error(this.__('There was a problem submitting the form.'))
        }
      }
    },

    async handleSubmit(formData, close) {
      try {
        const {
          data: { redirect, id },
        } = await this.createRequest(formData)

        Nova.success(
          this.__('The :resource was created!', {
            resource: this.resourceInformation.singularLabel.toLowerCase(),
          })
        )

        if (close) {
          this.$router.push({ path: redirect })
        } else {
          // Reset the form by refetching the fields
          this.validationErrors = new Errors()
          this.step = 0
          await this.getFields()
        }

      } catch (error) {
        if (error.response.status == 422) {
          this.validationErrors = new Errors(error.response.data.errors)
          Nova.error(this.__('There was a problem submitting the form.'))
        }
      }
    },

    /**
     * Send a checkpoint request for this resource
     */
    checkpointRequest(formData, session = true) {
      return Nova.request().post(
        `/nova-api/${this.resourceName}/step/${this.step}`,
        formData,
        {
          params: {
            editing: true,
            editMode: 'create',
            storeMode: 'checkpoint',
            viaSession: session,
            resourceId: this.resourceId,
          },
        }
      )
    },

    /**
     * Send a checkpoint request for this resource
     */
    createRequest(formData) {
      return Nova.request().post(
        `/nova-api/${this.resourceName}/step/${this.step}`,
        formData,
        {
          params: {
            editing: true,
            editMode: 'create',
            storeMode: 'submit',
            viaSession: false,
            resourceId: this.resourceId,
          },
        }
      )
    },
  },


  computed: {
    singularName() {
      if (this.relationResponse) {
        return this.relationResponse.singularLabel
      }

      return this.resourceInformation.singularLabel
    },

    isRelation() {
      return Boolean(this.viaResourceId && this.viaRelationship)
    },

    alreadyFilled() {
      return this.relationResponse && this.relationResponse.alreadyFilled
    },

    isHasOneRelationship() {
      return this.relationResponse && this.relationResponse.hasOneRelationship
    },

  },
}
</script>
