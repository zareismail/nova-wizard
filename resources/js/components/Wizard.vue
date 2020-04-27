<template>
  <div> 
    <wizard-nav v-if="navigation && steps.length > 1" :steps="steps" :current="current" :class="'mb-6'"/>

    <form v-if="panels" autocomplete="off" ref="form" @submit="handleSubmitAndClose"> 
      <form-panel
        class="mb-8" 
        v-if="currentPanel"
        @file-upload-started="handleFileUploadStarted"
        @file-upload-finished="handleFileUploadFinished"
        :shown-via-new-relation-modal="shownViaNewRelationModal"
        :panel="currentPanel"
        :name="currentPanel.name"
        :key="currentPanel.name"
        :resource-name="resourceName"
        :resource-id="resourceId"
        :fields="currentPanel.fields"
        mode="form"
        :validation-errors="validationErrors"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :via-relationship="viaRelationship"
      /> 

      <!-- Create Button -->
      <div class="flex items-center">
        <cancel-button @click="$emit('cancelled')" />

        <progress-button 
          dusk="previous-step"
          class="mr-3"
          @click.native="handlePreviousStep"
          v-if="current"
          :disabled="isWorking"
          :processing="false"
        >
          {{ __('Previous') }}
        </progress-button>

        <progress-button 
          dusk="next-step"
          class="mr-3"
          @click.native="handleNextStep"
          :disabled="current == steps.length - 1 || isWorking"
          v-if="current < steps.length - 1"
          :processing="isWorking"
        >
          {{ __('Next') }}
        </progress-button> 

        <progress-button 
          dusk="submit-and-stay-step"
          class="mr-3"
          @click.native="handleSubmitAndStay"
          :disabled="isWorking"
          v-if="current == steps.length - 1"
          :processing="wasSubmitedViaClose"
        >
          {{ submitAndStay }}
        </progress-button>

        <progress-button 
          dusk="submit-step"
          class="mr-3"
          @click.native="handleSubmitAndClose"
          :disabled="isWorking"
          v-if="current == steps.length - 1"
          :processing="submitd"
        >
          {{ submit }}
        </progress-button>
        

        <!-- <slot v-if="current == steps.length - 1" scope="buttons"></slot> -->
        
      </div>
    </form>
  </div>
</template>

<script>
import { 
  mapProps,
  Errors 
} from 'laravel-nova'
import HandlesUploads from './HandlesUploads'

export default {
  mixins: [HandlesUploads],
  props: {   
    navigation: { 
      type: Boolean,
      default: false,
    }, 

    nextHandler: { 
      type: Function,
      required: true,
    },

    previousHandler: { 
      type: Function,
      required: true,
    },

    submitHandler: { 
      type: Function,
      required: true,
    }, 

    submit: { 
      type: String,
      required: true,
      default: 'Submit',
    },

    submitAndStay: { 
      type: String,
      required: true,
      default: 'Submit And Stay',
    }, 

    panels: { 
      type: Array,
      default: [],
    }, 

    validationErrors: {
      type: Object,
      required: true,
      default: new Errors(),
    }, 

    ...mapProps([ 
      'resourceName',
      'resourceId',
      'viaResource',
      'viaResourceId',
      'viaRelationship',
    ])
  }, 

  data: () => ({
    wasSubmited: false,
    wasSubmitedViaClose: false, 
  }),

  methods: {  
    async handlePreviousStep() {
      this.isWorking = true;     

      await this.previousHandler()

      this.isWorking = false
    },

    async handleNextStep() { 
      this.isWorking = true;   

      await this.nextHandler(this.checkpointResourceFormData)  

      this.isWorking = false
    },

    async handleSubmitAndClose() {
      this.isWorking = true;     
      this.wasSubmited = true;     

      await this.submitHandler(this.checkpointResourceFormData, true)

      this.isWorking = false   
      this.wasSubmited = false;  
    },

    async handleSubmitAndStay() {
      this.isWorking = true;     
      this.wasSubmitedViaClose = true;     

      await this.submitHandler(this.checkpointResourceFormData, false)

      this.isWorking = false   
      this.wasSubmitedViaClose = false;  
    }, 
  },

  computed: {   
    current() {  
      return _.findIndex(this.steps, (step) => ! step.passed)
    },

    steps() {
      return this.panels.filter(panel => panel.step != undefined)
    },

    currentPanel() {  
      return this.steps[this.current]
    },  

    /**
     * Create the form data for creating the resource.
     */
    checkpointResourceFormData() {
      return _.tap(new FormData(), formData => {
        _.each(this.currentPanel.fields, field => {
          field.fill(formData)
        }) 

        formData.append('viaResource', this.viaResource)
        formData.append('viaResourceId', this.viaResourceId)
        formData.append('viaRelationship', this.viaRelationship)
      })
    },
  },
}
</script>
