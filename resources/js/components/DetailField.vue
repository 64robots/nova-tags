<template>
    <panel-item :field="field" />
</template>

<script>
import DisplayCommaSeparated from './DisplayCommaSeparated';

export default {
  mixins: [DisplayCommaSeparated],

  props: ['resource', 'resourceName', 'resourceId', 'field']
};
</script>
