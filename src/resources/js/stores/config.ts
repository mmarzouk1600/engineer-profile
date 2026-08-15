import { ref } from "vue";
import { defineStore } from "pinia";
import layoutConfig from "@/core/config/DefaultLayoutConfig";

export const useConfigStore = defineStore("config", () => {
  const config = ref(layoutConfig);

  return {
    config
  };
});
