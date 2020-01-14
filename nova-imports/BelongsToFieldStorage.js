export default {
    fetchAvailableResources(resourceName, fieldAttribute, params) {
      return Nova.request().get(
        `/nova-r64-tags/${resourceName}/associatable/${fieldAttribute}`,
        params
      );
    },
  
    determineIfSoftDeletes(resourceName) {
      return Nova.request().get(`/nova-api/${resourceName}/soft-deletes`);
    }
  };
  