<template>
  <default-field :field="field">
    <template slot="field">
      <Multiselect
        v-model="value"
        :tag-placeholder="tagPlaceholder"
        :placeholder="placeholder"
        :label="label"
        :track-by="key"
        :options="availableResources"
        :multiple="true"
        :taggable="true"
        @tag="addTag"
      />

      <p v-if="hasError" class="my-2 text-danger">
        {{ firstError }}
      </p>
    </template>
  </default-field>
</template>

<script>
import 'vue-multiselect/dist/vue-multiselect.min.css';
import Multiselect from 'vue-multiselect';
import FormField from 'laravel-nova/src/mixins/FormField';
import HandlesValidationErrors from 'laravel-nova/src/mixins/HandlesValidationErrors';
import storage from '../../../nova-imports/BelongsToFieldStorage';

export default {
  components: { Multiselect },

  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data() {
    return {
      availableResources: []
    };
  },

  computed: {
    key() {
      return this.field.valueKey || 'id';
    },

    label() {
      return this.field.labelKey || 'name';
    },

    placeholder() {
      return this.field.placeholder === undefined
        ? this.__('Search or add a tag')
        : this.field.placeholder;
    },

    tagPlaceholder() {
      return this.field.tagPlaceholder === undefined
        ? this.__('Add this as new tag')
        : this.field.tagPlaceholder;
    }
  },

  created() {
    this.getAvailableResources();
  },

  methods: {
    /*
    * Add a new tag with random id
    */
    addTag(newTag) {
      const tag = {
        [this.label]: newTag,
        [this.key]:
          newTag.substring(0, 2) + Math.floor(Math.random() * 10000000)
      };
      this.availableResources.push(tag);
      this.value.push(tag);
    },

    /*
    * Set the initial, internal value for the field.
    */
    setInitialValue() {
      let resources = this.field.value || [];

      resources = resources.map(resource => ({
        [this.key]: resource[this.key],
        [this.label]: resource[this.label]
      }));

      this.value = resources;
    },

    /**
     * Get the resources that may be related to this resource.
     */
    getAvailableResources() {
      return storage
        .fetchAvailableResources(
          this.resourceName,
          this.field.attribute,
          this.queryParams
        )
        .then(({ data: { resources } }) => {
          this.availableResources = resources.map(resource => ({
            [this.key]: resource.value,
            [this.label]: resource.display
          }));
        });
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      formData.append(this.field.attribute, JSON.stringify(this.value) || '');
    }
  }
};
</script>
