export default {
  created() {
    const values = this.field.value.map(
      field => (this.field.labelKey ? field[this.field.labelKey] : field)
    );
    this.$set(this.field, 'value', values.join(', '));
  }
};
