<template>
  <span>{{ field.value }}</span>
</template>

<script>
import DisplayCommaSeparated from './DisplayCommaSeparated';

export default {
  mixins: [DisplayCommaSeparated],

  props: ['resourceName', 'field']
};
</script>
