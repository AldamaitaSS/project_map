<template>
  <header class="header">
    <i class="fas fa-sign-out-alt logout-icon" @click="logout"></i>
  </header>
</template>

<script>
export default {
  methods: {
    logout() {
      alert("Logout clicked");
    }
  }
}
</script>