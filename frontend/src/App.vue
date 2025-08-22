<template>
  <div id="app">
    <!-- Ini tempat Vue Router me-render komponen sesuai URL -->
    <router-view :key="$route.fullPath"></router-view>
  </div>
</template>

<script>
export default {
  name: "App",
};
</script>
