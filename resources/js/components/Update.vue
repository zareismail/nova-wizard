<template>
  <loading-view :loading="loading">
    <custom-update-header
      class="mb-3"
      :resource-name="resourceName"
      :resource-id="resourceId"
    />

    <wizard-form 
      @update-last-retrieved-at-timestamp="updateLastRetrievedAtTimestamp"
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
      :submit="__('Update :resource', { resource: singularName })"
      :submit-and-stay="__('Update & Continue Editing')"
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

  props: mapProps([
    'resourceName',
    'resourceId',
    'viaResource',
    'viaResourceId',
    'viaRelationship',
  ]),

  data: () => ({
    relationResponse: null,
    loading: true, 
    validationErrors: new Errors(),
    lastRetrievedAt: null,
  }),

  async created() { 
    if (Nova.missingResource(this.resourceName))
      return this.$router.push({ name: '404' })

    this.initializeStep()

    // If this update is via a relation index, then let's grab the field
    // and use the label for that as the one we use for the title and buttons
    if (this.isRelation) {
      const { data } = await Nova.request(
        `/nova-api/${this.viaResource}/field/${this.viaRelationship}`
      )
      this.relationResponse = data
    }

    this.getFields()
    this.updateLastRetrievedAtTimestamp()
  },

  methods: {
    /**
     * Get the available fields for the resource.
     */
    async getFields() {
      this.loading = true

      this.panels = []
      this.fields = []

      const {
        data: { panels, fields },
      } = await Nova.request()
        .get(
          `/nova-api/${this.resourceName}/${this.resourceId}/update-fields`, 
          {
            params: {
              step: this.step,
              editing: true,
              editMode: 'update',
              viaResource: this.viaResource,
              viaResourceId: this.viaResourceId,
              viaRelationship: this.viaRelationship,
            },
          }
        )
        .catch(error => {
          if (error.response.status == 404) {
            this.$router.push({ name: '404' })
            return
          }
        })

      this.panels = panels
      this.fields = fields
      this.loading = false

      Nova.$emit('resource-loaded')
    },  

    async handleSubmit(formData, close) {
      this.handleNext(formData, true, close)
    }, 

    /**
     * Update the resource using the provided data.
     */
    async handleNext(formData, submit = false) {  
      try {
        const {
          data: { redirect },
        } = await this.updateRequest(formData)

        Nova.success(
          this.__('The :resource was updated!', {
            resource: this.resourceInformation.singularLabel.toLowerCase(),
          })
        )

        await this.updateLastRetrievedAtTimestamp() 

        if(submit) {
          this.$router.push({ path: redirect }) 
        } 
        else { 
          this.step = submit ? 0 : this.step + 1;

          this.validationErrors = new Errors() 

          await this.getFields()  
        } 
      } catch (error) {  
        if (error.response.status == 422) {
          this.validationErrors = new Errors(error.response.data.errors)
          Nova.error(this.__('There was a problem submitting the form.'))
        }

        if (error.response.status == 409) {
          Nova.error(
            this.__(
              'Another user has updated this resource since this page was loaded. Please refresh the page and try again.'
            )
          )
        }
      } 
    },

    /**
     * Send an update request for this resource
     */
    updateRequest(formData) { 
      return Nova.request().post(
        `/nova-api/${this.resourceName}/${this.resourceId}`,
        _.tap(formData, (formData) => { 
          formData.append('_method', 'PUT')
          formData.append('_retrieved_at', this.lastRetrievedAt) 
        }),
        {
          params: {
            step: this.step,
            editing: true,
            editMode: 'update',
            viaResource: this.viaResource,
            viaResourceId: this.viaResourceId,
            viaRelationship: this.viaRelationship,
          },
        }
      )
    },

    /**
     * Update the last retrieved at timestamp to the current UNIX timestamp.
     */
    updateLastRetrievedAtTimestamp() {
      this.lastRetrievedAt = Math.floor(new Date().getTime() / 1000)
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
  },
}
</script>
