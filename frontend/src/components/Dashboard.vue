<template>
  <div class="main">
    <!-- Header -->
    <div class="header">
      <i class="fas fa-sign-out-alt logout-icon" @click="logout"></i>
    </div>

    <!-- Toolbar -->
    <h1>Dashboard</h1>
    <div class="toolbar">
      <div class="toolbar-left">
        <div class="search-tools">
          <input
            type="text"
            ref="searchInput"
            placeholder="Search Location"
            @keyup.enter="searchLocation"
          />
          <button class="btn-icon" @click="toggleAddMarker">
            <i class="fas fa-map-marker-alt"></i>
          </button>
          <button class="btn-icon" @click="togglePolygonMode" :class="{ active: drawingPolygon }">
            <i class="fas fa-project-diagram"></i>
          </button>
          <button class="btn-icon" @click="clearPolygon" v-if="drawingPolygon || polygon">
            <i class="fas fa-trash"></i> Clear
          </button>
          <button class="btn-icon" @click="finishPolygon" v-if="drawingPolygon && polygonPath.length >= 3">
            <i class="fas fa-check"></i> Finish
          </button>
        </div>
      </div>
      <div class="toolbar-right">
        <button class="btn-danger">
          <i class="fas fa-file-import"></i> Import File
        </button>
        <button class="btn-success" @click="saveProject">
          <i class="fas fa-save"></i> Save Project
        </button>
      </div>
    </div>

    <!-- Instructions -->
    <div v-if="drawingPolygon" class="instructions">
      <p>
        <strong>Drawing Polygon Mode:</strong>
      </p>
    </div>

    <!-- Map -->
    <div class="map-container">
      <div id="map" style="height: 500px;"></div>
    </div>

    <!-- Footer -->
    <div class="footer">
      2025. All rights reserved.
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      map: null,
      addingMarker: false,
      drawingPolygon: false,
      placemarks: [],
      markers: [],
      polygon: null,
      polygonPath: [],
      polygonMarkers: [],
      polyline: null,
      searchMarker: null,
      apiKey: "AIzaSyA99VXYKlRV5wCubuIyXWdTGhSwkfyqeSc"
    };
  },
  methods: {
    logout() {
      alert("Logout clicked");
    },

    toggleAddMarker() {
      this.addingMarker = !this.addingMarker;
      this.drawingPolygon = false;
      this.updateMapCursor();
      console.log('Toggle add marker:', this.addingMarker);
    },

    togglePolygonMode() {
      console.log('Toggle polygon mode - sebelum:', this.drawingPolygon);
      
      if (!this.drawingPolygon) {
        // Mulai mode polygon
        this.drawingPolygon = true;
        this.addingMarker = false;
        this.clearPolygon();
        console.log('POLYGON MODE AKTIF - Klik di peta untuk menambah titik');
      } else {
        // Stop mode polygon
        this.drawingPolygon = false;
        console.log('POLYGON MODE NONAKTIF');
      }
      
      console.log('Toggle polygon mode - sesudah:', this.drawingPolygon);
    },

    updateMapCursor() {
      // Ubah cursor map sesuai mode
      if (this.map) {
        if (this.drawingPolygon) {
          this.map.setOptions({ draggableCursor: 'crosshair' });
        } else if (this.addingMarker) {
          this.map.setOptions({ draggableCursor: 'default' });
        } else {
          this.map.setOptions({ draggableCursor: 'grab' });
        }
      }
    },

    addPolygonPoint(lat, lng) {
      console.log('=== ADDING POLYGON POINT ===');
      console.log('Koordinat:', lat, lng);
      console.log('Drawing polygon status:', this.drawingPolygon);
      
      // Tambah ke array
      this.polygonPath.push({ lat: lat, lng: lng });
      console.log('Polygon path sekarang:', this.polygonPath);
      
      // Buat titik bulat dengan Circle
      const circle = new google.maps.Circle({
        center: { lat: lat, lng: lng },
        radius: 3, // radius dalam meter
        map: this.map,
        fillColor: '#FF0000',
        fillOpacity: 0.8,
        strokeColor: '#FFFFFF',
        strokeWeight: 2
      });
      
      this.polygonMarkers.push(circle);
      console.log('Circle ditambahkan, total marker:', this.polygonMarkers.length);
      
      // Jika sudah ada 2 titik atau lebih, buat garis
      if (this.polygonPath.length >= 2) {
        console.log('Membuat garis karena sudah ada', this.polygonPath.length, 'titik');
        this.drawLine();
      }
    },

    drawLine() {
      console.log('=== DRAWING LINE ===');
      
      // Hapus garis lama
      if (this.polyline) {
        this.polyline.setMap(null);
        console.log('Garis lama dihapus');
      }
      
      // Buat garis baru
      console.log('Membuat garis dengan koordinat:', this.polygonPath);
      
      this.polyline = new google.maps.Polyline({
        path: this.polygonPath,
        strokeColor: '#FF0000',
        strokeOpacity: 1.0,
        strokeWeight: 4,
        geodesic: false
      });
      
      this.polyline.setMap(this.map);
      console.log('Garis berhasil dibuat dan ditampilkan!');
    },

    removePolygonPoint(circle) {
      const index = this.polygonMarkers.indexOf(circle);
      if (index >= 0) {
        // Hapus circle dari peta
        circle.setMap(null);
        
        // Hapus dari arrays
        this.polygonMarkers.splice(index, 1);
        this.polygonPath.splice(index, 1);
        
        // Update garis
        if (this.polygonPath.length >= 2) {
          this.drawLine();
        } else {
          // Hapus garis jika kurang dari 2 titik
          if (this.polyline) {
            this.polyline.setMap(null);
            this.polyline = null;
          }
        }
        
        console.log('Titik dihapus, sisa:', this.polygonPath.length);
      }
    },

    finishPolygon() {
      if (this.polygonPath.length < 3) {
        alert('Minimal 3 titik diperlukan untuk membuat polygon');
        return;
      }

      console.log('Menyelesaikan polygon dengan', this.polygonPath.length, 'titik');

      // Hapus garis sementara
      if (this.polyline) {
        this.polyline.setMap(null);
        this.polyline = null;
      }

      // Buat polygon final dengan area yang bisa di-select
      this.polygon = new google.maps.Polygon({
        paths: this.polygonPath,
        strokeColor: "#FF0000",
        strokeOpacity: 0.8,
        strokeWeight: 3,
        fillColor: "#FF0000",
        fillOpacity: 0.3, // Transparansi untuk bisa lihat area
        editable: true,
        draggable: false,
        clickable: true // Bisa diklik untuk select
      });
      
      this.polygon.setMap(this.map);

      // Event listener untuk select polygon
      this.polygon.addListener('click', () => {
        console.log('Polygon diklik/terpilih');
        // Ubah warna saat dipilih
        this.polygon.setOptions({
          fillOpacity: 0.5,
          strokeWeight: 4
        });
      });

      // Event listener untuk perubahan polygon saat di-edit
      this.polygon.getPath().addListener('set_at', () => {
        this.updatePolygonPath();
      });

      this.polygon.getPath().addListener('insert_at', () => {
        this.updatePolygonPath();
      });

      // Hapus titik-titik bulat (polygon sudah jadi)
      this.polygonMarkers.forEach(circle => circle.setMap(null));
      this.polygonMarkers = [];

      this.drawingPolygon = false;
      this.updateMapCursor();
      
      console.log('Polygon berhasil dibuat! Area bisa dipilih dan di-edit.');
    },

    updatePolygonPath() {
      if (this.polygon) {
        this.polygonPath = this.polygon.getPath().getArray().map(coord => ({
          lat: coord.lat(),
          lng: coord.lng()
        }));
        console.log('Polygon path updated:', this.polygonPath.length, 'titik');
      }
    },

    clearPolygon() {
      console.log('=== CLEAR POLYGON ===');
      
      // Hapus semua marker/circle
      this.polygonMarkers.forEach(item => {
        item.setMap(null);
      });
      this.polygonMarkers = [];
      
      // Hapus garis
      if (this.polyline) {
        this.polyline.setMap(null);
        this.polyline = null;
      }
      
      // Hapus polygon jika ada
      if (this.polygon) {
        this.polygon.setMap(null);
        this.polygon = null;
      }
      
      // Reset array
      this.polygonPath = [];
      
      console.log('Polygon dibersihkan');
    },

    updatePolyline() {
      console.log('Update polyline called with', this.polygonPath.length, 'points');
      
      // Hapus polyline lama jika ada
      if (this.polyline) {
        this.polyline.setMap(null);
        this.polyline = null;
        console.log('Polyline lama dihapus');
      }

      // Buat polyline baru jika ada minimal 2 titik
      if (this.polygonPath.length >= 2) {
        console.log('Membuat polyline dengan koordinat:', this.polygonPath);
        
        this.polyline = new google.maps.Polyline({
          path: this.polygonPath,
          geodesic: false,
          strokeColor: '#0000FF',
          strokeOpacity: 1.0,
          strokeWeight: 3
        });
        
        this.polyline.setMap(this.map);
        console.log('Polyline berhasil dibuat dan ditampilkan');
      } else {
        console.log('Tidak cukup titik untuk membuat polyline');
      }
    },

    initMap() {
      this.map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: -7.983908, lng: 112.621391 },
        zoom: 13,
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true
      });

      // Event klik di peta - PALING PENTING!
      this.map.addListener("click", (e) => {
        console.log('=== MAP CLICKED ===');
        const lat = e.latLng.lat();
        const lng = e.latLng.lng();
        console.log('Koordinat klik:', lat, lng);
        console.log('Status drawing polygon:', this.drawingPolygon);
        console.log('Status adding marker:', this.addingMarker);

        // Cek kondisi dengan lebih eksplisit
        if (this.drawingPolygon === true) {
          console.log('KONDISI POLYGON TRUE - memanggil addPolygonPoint');
          this.addPolygonPoint(lat, lng);
          return;
        }
        
        if (this.addingMarker === true) {
          console.log('KONDISI MARKER TRUE - memanggil addMarker');
          this.addMarker(lat, lng);
          return;
        }
        
        console.log('TIDAK ADA KONDISI YANG TERPENUHI');
        console.log('drawingPolygon type:', typeof this.drawingPolygon);
        console.log('addingMarker type:', typeof this.addingMarker);
      });

      // Event double click untuk menyelesaikan polygon
      this.map.addListener("dblclick", (e) => {
        if (this.drawingPolygon && this.polygonPath.length >= 3) {
          e.stop(); // prevent default zoom
          console.log('Double click - finishing polygon');
          this.finishPolygon();
        }
      });

      // Event right click di peta untuk cancel mode
      this.map.addListener("rightclick", (e) => {
        if (this.drawingPolygon) {
          // Right click di area kosong = cancel drawing
          this.clearPolygon();
        }
      });

      console.log('Map initialized');
    },

    addMarker(lat, lng) {
      const greenIcon = {
        url: "http://maps.google.com/mapfiles/ms/icons/green-dot.png"
      };
      
      const marker = new google.maps.Marker({
        position: { lat, lng },
        map: this.map,
        icon: greenIcon,
        draggable: true
      });

      this.markers.push(marker);
      this.placemarks.push({ lat, lng });

      // Geocoding untuk mendapatkan alamat
      const geocoder = new google.maps.Geocoder();
      geocoder.geocode({ location: { lat, lng } }, (results, status) => {
        if (status === "OK" && results[0]) {
          console.log("Alamat:", results[0].formatted_address);
        }
      });

      this.addingMarker = false;
      this.updateMapCursor();
      console.log('Marker added at:', lat, lng);
    },

    searchLocation() {
      const query = this.$refs.searchInput.value.trim();
      if (!query) {
        alert('Masukkan lokasi untuk pencarian');
        return;
      }

      const geocoder = new google.maps.Geocoder();
      geocoder.geocode({ address: query }, (results, status) => {
        if (status === "OK" && results[0]) {
          const location = results[0].geometry.location;
          this.map.setCenter(location);
          this.map.setZoom(16);

          // Hapus marker pencarian sebelumnya
          if (this.searchMarker) {
            this.searchMarker.setMap(null);
          }

          // Buat marker biru untuk hasil pencarian
          this.searchMarker = new google.maps.Marker({
            map: this.map,
            position: location,
            icon: {
              url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
            },
            title: results[0].formatted_address
          });

          // Clear search input
          this.$refs.searchInput.value = '';
          
          console.log('Location found:', results[0].formatted_address);
        } else {
          alert("Lokasi tidak ditemukan: " + status);
        }
      });
    },

    saveProject() {
      let dataToSave = {
        placemarks: this.placemarks,
        timestamp: new Date().toISOString()
      };

      // Jika ada polygon, simpan koordinatnya
      if (this.polygon) {
        const polygonCoords = this.polygon.getPath().getArray().map(coord => ({
          lat: coord.lat(),
          lng: coord.lng()
        }));
        dataToSave.polygon = polygonCoords;
        console.log('Saving polygon with', polygonCoords.length, 'points');
      }

      console.log('Data to save:', dataToSave);

      fetch("http://localhost:8000/project_map/backend/api/save_project.php", {
        method: "POST",
        headers: { 
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify(dataToSave)
      })
      .then((res) => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then((data) => {
        if (data.success) {
          alert("Project berhasil disimpan!");
          console.log('Project saved successfully');
        } else {
          alert("Gagal menyimpan project: " + (data.message || 'Unknown error'));
        }
      })
      .catch((error) => {
        console.error('Save error:', error);
        alert("Terjadi error saat menyimpan: " + error.message);
      });
    }
  },

  mounted() {
    // Setup keyboard events
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (this.drawingPolygon) {
          this.clearPolygon();
        } else if (this.addingMarker) {
          this.addingMarker = false;
          this.updateMapCursor();
        }
      }
    });

    // Initialize Google Maps
    window.initMap = this.initMap.bind(this);
    const existingScript = document.querySelector('script[src*="maps.googleapis.com"]');
    
    if (!existingScript) {
      const script = document.createElement("script");
      script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&libraries=places&callback=initMap`;
      script.async = true;
      script.defer = true;
      script.onerror = () => {
        console.error('Failed to load Google Maps API');
        alert('Failed to load Google Maps. Please check your API key and internet connection.');
      };
      document.head.appendChild(script);
    } else {
      this.initMap();
    }
  }
};
</script>