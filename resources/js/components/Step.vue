<template> 
	<div class="flex items-center" :class="{'w-full': ! last, 'w-24': last}">
	  <div 
	  	@click="$emit('clicked', step)"
	  	class="flex items-center w-24 h-step rounded-full flex items-center justify-center shadow-inner bg-40 text-80 -mr-px -ml-px btn btn-link cursor-pointer" 
	  	:class="{
		  	'border border-success bg-white text-success': step.passed,  
		  	'border border-info bg-white text-info': active,  
			}"
		><span class="min-w-24 px-2 text-center">{{
	    step.name
	  }}</span></div>
	  <div v-if="! last" class="w-full py-1 shadow-inner bg-40 text-80" :class="{
		  'border-t border-b border-success bg-white text-success': step.passed,  
		  'border-t border-b border-info bg-white text-info': active, 
		}"></div> 
	</div>
</template>

<script> 
export default {
	props: {
		step: {
			type: Object,
			required: true,
		},
		active: {
			type: Boolean,
			default: false,
		}, 
		last: {
			type: Boolean,
			default: false,
		},
	},

  mounted() {
  }, 

  computed: { 
  }
}
</script>
<style> 
.h-step {
	height: 6rem;
} 
</style>
