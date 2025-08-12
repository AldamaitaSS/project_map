<template>
  <div>
    <h2>Data Lokasi</h2>
    <ul>
      <li v-for="lokasi in dataLokasi" :key="lokasi.id">
        {{ lokasi.nama }} - {{ lokasi.koordinat }}
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  data() {
    return { dataLokasi: [] }
  },
  mounted() {
    fetch("http://localhost:8000/api/get-data.php")
      .then(res => res.json())
      .then(data => this.dataLokasi = data)
  }
}
</script>
