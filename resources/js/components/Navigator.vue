<template> 
  <card class="flex justify-center shadow-md p-2">
    <div class="flex justify-arround w-full" :class="{
      'max-w-lg': steps.length < 3,
      'max-w-xl': steps.length >= 3 && steps.length < 5,
    }">
      <wizard-step  
        v-for="(step, index) in steps" 
        :step="step" 
        :key="step.key" 
        :active="current === index" 
        :last="steps.length - 1 === index"
        @clicked="handleClick"
      /> 
    </div>
  </card>
</template>

<script> 
export default {
  props: {
    steps: {
      type: Array,
      required: true,
      default: [],
    },
    current: {
      type: [String, Number],
      default: undefined,
    }
  },

  methods: {
    isActive(step, index) {   
      return this.current === index;
    },

    isLastStep(step) {   
      return this.steps.indexOf(step) === this.steps.length - 1;
    },

    handleClick(step) {  
      if(this.steps.indexOf(step) < this.steps.indexOf(this.current)) {
        this.$emit('previous') 
      }
      else {
        this.$emit('next') 
      } 
    } 
  }, 
}
</script>
