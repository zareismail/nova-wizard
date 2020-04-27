export default { 
  data: () => ({ 
    fields: [],
    panels: [],
    step: 0,
  }),

  methods: { 
    initializeStep() { 
      if(this.lastStep > this.step) { 
        this.step = this.lastStep; 
      }
      if(this.step < 0) {
        this.step = 0;
      }
    }, 

    async handlePrevious() {   
      this.step && --this.step

      await this.getFields()
    },
  },

  computed: {
    lastStep() { 
      return this.$route.params.step;
    }, 

    currentPanel() {
      return this.panelsWithFields[this.step];
    },

    panelsWithFields() {
      return _.map(this.panels, panel => {
        return {
          name: panel.name,
          step: panel.step,
          passed: panel.passed,
          checkpoint: panel.checkpoint,
          fields: _.filter(this.fields, field => field.panel == panel.name),
        }
      }).filter(panel => panel.step != undefined)
    },
  },
}
