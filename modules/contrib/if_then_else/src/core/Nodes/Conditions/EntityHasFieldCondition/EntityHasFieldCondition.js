class EntityHasFieldConditionControl extends Rete.Control {
  constructor(emitter, key) {
    super(key);
    this.component = {
      props: ['emitter', 'ikey', 'getData', 'putData'],
      components: {
        Multiselect: window.VueMultiselect.default
      },
      template: `
    <div class="fields-container">
      <div class="form-fields-selection" >
        <div class="radio">
          <input type="radio" id="one" value="list" v-model="field_selection" @change="fieldSelectionChanged">
          <label for="one">Select Field List</label>
        </div>
        <multiselect v-if="field_selection=='list'" v-model="value" :show-labels="false" tag-placeholder="Add this as new tag" placeholder="Select Fields" label="name" track-by="code" :options="options" :taggable="true" @input="updateSelected" @tag="addTag"></multiselect>
        <br>
        <div class="radio">
          <input type="radio" id="two" value="input" v-model="field_selection" @change="fieldSelectionChanged">
          <label for="two">Enter Field Name</label>
        </div>
        <input type="text" v-model='valueText' @blur="valueTextChanged" placeholder="Enter field machine names" v-if="field_selection=='input'" />
      </div>
    </div>`,
      data() {
        return {
          type: drupalSettings.if_then_else.nodes.entity_has_field_condition.type,
          class: drupalSettings.if_then_else.nodes.entity_has_field_condition.class,
          name: drupalSettings.if_then_else.nodes.entity_has_field_condition.name,
          value: [],
          options: [],
          entity_fields: [],
          field_selection: 'list',
          valueText: '',
        }
      },
      methods: {
        addTag (newTag) {
          //Multiselect tags
          const tag = {
            name: newTag,
            code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
          };
          this.options.push(tag);
          this.value.push(tag)
        },
        updateSelected(value){
          //Triggered when changing field values
          var selectedOptions = [];
          selectedOptions.push({name: value.name, code: value.code});
          this.putData('entity_fields',selectedOptions);
          editor.trigger('process');
        },
        fieldSelectionChanged(){
          this.putData('field_selection',this.field_selection);
          editor.trigger('process');
        },
        valueTextChanged(){
          this.putData('valueText',this.valueText);
          editor.trigger('process');
        }
      },
      mounted() {
        //initialize variable for data
        this.putData('type',drupalSettings.if_then_else.nodes.entity_has_field_condition.type);
        this.putData('class',drupalSettings.if_then_else.nodes.entity_has_field_condition.class);
        this.putData('name', drupalSettings.if_then_else.nodes.entity_has_field_condition.name);

        //setting values of selected fields when rule edit page loads.
        var get_entity_fields = this.getData('entity_fields');
        if(typeof get_entity_fields != 'undefined'){
          this.value = this.getData('entity_fields');
        }else{
          this.putData('entity_fields',[]);
        }

        // Get value of radio button
        var get_field_selection = this.getData('field_selection');
        if(typeof get_field_selection != 'undefined'){
          this.field_selection = this.getData('field_selection');
        }else{
          this.putData('field_selection', 'list');
        }

        this.valueText = this.getData('valueText');

      },
      created() {
        if(drupalSettings.if_then_else.nodes.entity_has_field_condition.entity_fields){
          //setting list of all fields for a form when rule edit page loads.
          this.options = drupalSettings.if_then_else.nodes.entity_has_field_condition.entity_fields;
        }
      }
    };
    this.props = { emitter, ikey: key };
  }

  //setting list value of fields. Used when changing entity or bundle value in condition node.
  setOptions(options) {
    this.vueContext.options = options;
  }
  
  //resetting selected fields value when changing the entity or bundle value in condition node.
  setValue(value){
    this.vueContext.value = value;
  }
}