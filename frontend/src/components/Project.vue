<template>
<div class="project-container" id="ProjectPage">
  <div class="sidebar">
    <div class="sidebar-menu">
      <!-- Project Info -->
      <div class="project-info">
        <h3>{{ currentProject ? currentProject.nama_project : 'Current Project' }}</h3>
        <p class="project-id">ID: {{ currentProject ? currentProject.id_project : 'N/A' }}</p>
      </div>

      <!-- Placemarks Section -->
      <div class="sidebar-section">
        <div class="section-header" @click="toggleSection('placemarks')">
          <h4>
            <i class="fas fa-map-marker-alt"></i> 
            Placemarks ({{ placemarks.length }})
          </h4>
          <button 
            class="btn-toggle" 
            :class="{ active: showPlacemarks }">
            <i class="fas fa-chevron-down"></i>
          </button>
        </div>
        
        <div v-if="showPlacemarks" class="section-content">
          <div 
            v-if="placemarks.length === 0" 
            class="empty-message">
            <i class="fas fa-map-marker-alt"></i>
            <p>No placemarks yet</p>
          </div>
          
          <div v-if="placemarks.length > 0" class="placemark-list">
            <div 
              v-for="(placemark, index) in placemarks" 
              :key="index" 
              class="sidebar-item placemark-item">
              <div class="item-info">
                <div class="item-icon">
                  <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="item-details">
                  <h5>Marker {{ index + 1 }}</h5>
                  <p class="coordinates">{{ placemark.lat.toFixed(6) }}, {{ placemark.lng.toFixed(6) }}</p>
                  <p class="address" v-if="placemark.address">{{ placemark.address }}</p>
                </div>
              </div>
              <div class="item-actions">
                <button class="btn-action btn-focus" @click="focusOnPlacemark(index)" title="Focus">
                  <i class="fas fa-crosshairs"></i>
                </button>
                <button class="btn-action btn-edit" @click="editPlacemark(index)" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn-action btn-delete" @click="deletePlacemark(index)" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Polygons Section -->
      <div class="sidebar-section">
        <div class="section-header" @click="toggleSection('polygons')">
          <h4><i class="fas fa-draw-polygon"></i> Polygons ({{ polygons.length }})</h4>
          <button class="btn-toggle" :class="{ active: showPolygons }">
            <i class="fas fa-chevron-down"></i>
          </button>
        </div>
        
        <div v-if="polygons.length === 0 && showPolygons" class="empty-message">
          <i class="fas fa-draw-polygon"></i>
          <p>No polygons available</p>
        </div>

        <div v-if="showPolygons && polygons.length > 0" class="section-content">
          <div v-for="(polygonData, index) in polygons" :key="polygonData.id || index" class="sidebar-item polygon-item">
            <div class="item-info">
              <div class="item-icon">
                <i class="fas fa-draw-polygon"></i>
              </div>
              <div class="item-details">
                <h5>{{ polygonData.nama_polygon || `Polygon ${index + 1}` }}</h5>
                <p class="coordinates">{{ polygonData.coordinates ? polygonData.coordinates.length : 0 }} points</p>
                <p class="address" v-if="polygonData.deskripsi">{{ polygonData.deskripsi }}</p>
              </div>
            </div>
            <div class="item-actions">
              <button class="btn-action btn-focus" @click="focusOnPolygon(index)" title="Focus">
                <i class="fas fa-crosshairs"></i>
              </button>
              <button class="btn-action btn-edit" @click="editPolygon(index)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn-action btn-delete" @click="deletePolygon(index)" title="Delete">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="main">
    <!-- Header -->
    <div class="header">
      <div class="logo">
          <i class="fas fa-network-wired"></i>
          <span class="net">NET</span><span class="map">MAP</span>
        </div>
      <i class="fas fa-sign-out-alt logout-icon" @click="logout"></i>
    </div>

    <!-- Toolbar -->
    <h1>Project</h1>
    <div class="toolbar">
      <div class="toolbar-left">
        <div class="search-tools">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search Location"
            @keyup.enter="searchLocation"
          />
          <button class="btn-icon" @click="toggleAddMarker" :class="{ active: addingMarker }">
            <i class="fas fa-map-marker-alt"></i>
          </button>
          <button class="btn-icon" @click="togglePolygonMode" :class="{ active: drawingPolygon }">
            <i class="fas fa-draw-polygon"></i>
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
        <button class="btn-danger" @click="showImportDialog = true">
          <i class="fas fa-file-import"></i> Import File
        </button>
        <button class="btn-success" @click="saveProject">
          <i class="fas fa-save"></i> Save Project
        </button>
      </div>
    </div>

    <!-- Import Dialog -->
    <div v-if="showImportDialog" class="modal-overlay" @click="showImportDialog = false">
      <div class="modal-content" @click.stop>
        <h3>Import Project</h3>
        <input type="file" @change="importFile" accept=".json" />
        <div class="modal-actions">
          <button @click="showImportDialog = false" class="btn-secondary">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Instructions -->
    <div v-if="drawingPolygon" class="instructions">
      <p>
        <strong>Drawing Polygon Mode:</strong> Klik di peta untuk menambah titik polygon
      </p>
    </div>

    <!-- Loading Indicator -->
    <div v-if="loading" class="loading">
      <i class="fas fa-spinner fa-spin"></i> {{ loadingMessage }}
    </div>

    <!-- Map -->
    <div class="map-container">
      <div id="map" style="height: 450px;"></div>
    </div>
  </div>
</div>
</template>


<script>
export default {
  name: "Project",
  props: {
    currentProject: Object
  },
  data() {
    return {
      // Map related
      map: null,
      addingMarker: false,
      drawingPolygon: false,
      placemarks: [],
      markers: [],
      polygons: [], // Changed to array to support multiple polygons
      polygon: null, // Keep for backward compatibility
      polygonPath: [],
      polygonMarkers: [],
      polyline: null,
      searchMarker: null,
      apiKey: "AIzaSyA99VXYKlRV5wCubuIyXWdTGhSwkfyqeSc",
      
      // Backend integration
      baseUrl: "http://localhost:8000",
      savedProjects: [],
      currentProjectId: null,
      currentProject: null,
      showImportDialog: false,
      loading: false,
      loadingMessage: '',
      currentUser: null,

      // Sidebar states
      showPlacemarks: true,
      showPolygons: true,
      polygonArea: null,
      currentPolygonIndex: -1, // Track which polygon is being edited
      searchQuery: '' // For search input
    };
  },
  
  async mounted() {
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
      script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&libraries=places,geometry&callback=initMap`;
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

    const saved = localStorage.getItem('currentProject');
    if (saved) {
      this.currentProject = JSON.parse(saved);
    }

    // Load project data from route parameter
    const projectId = this.$route.params.id;
    if (projectId) {
      this.currentProjectId = projectId;
      // Wait a bit for map to be initialized
      setTimeout(() => {
        this.loadProjectData(projectId);
      }, 1000);
    }
  },

  watch: {
    '$route.params.id'(newId) {
      if (newId && this.map) {
        this.loadProjectData(newId);
      }
    }
  },

  methods: {
    async apiCall(endpoint, method = 'GET', data = null) {
      this.loading = true;
      this.loadingMessage = 'Processing...';
      
      try {
        const config = {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        };
        
        if (data && method !== 'GET') {
          config.body = JSON.stringify(data);
        }
        
        const response = await fetch(`${this.baseUrl}${endpoint}`, config);
        
        // Log untuk debugging
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
          // Coba baca response sebagai text untuk error message yang lebih baik
          const errorText = await response.text();
          console.error('Server error response:', errorText);
          throw new Error(`HTTP ${response.status}: ${errorText || response.statusText}`);
        }
        
        // Cek content-type sebelum parse JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          const textResponse = await response.text();
          console.warn('Non-JSON response received:', textResponse);
          throw new Error(`Server returned non-JSON response: ${contentType}`);
        }
        
        // Dapatkan response text terlebih dahulu
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        // Cek apakah response kosong
        if (!responseText.trim()) {
          throw new Error('Server returned empty response');
        }
        
        // Parse JSON dengan error handling yang lebih baik
        let result;
        try {
          result = JSON.parse(responseText);
        } catch (parseError) {
          console.error('JSON parse error:', parseError);
          console.error('Response text that failed to parse:', responseText);
          throw new Error(`Invalid JSON response from server: ${parseError.message}`);
        }
        
        this.loading = false;
        return result;
        
      } catch (error) {
        this.loading = false;
        console.error('API Error:', error);
        
        // Tampilkan error message yang lebih informatif
        const errorMessage = error.message || 'Unknown error occurred';
        alert(`API Error: ${errorMessage}`);
        
        return { success: false, error: errorMessage };
      }
    },

    // Load all projects from backend
    async loadAllProjects() {
      const result = await this.apiCall('/backend/api/project/readOne.php');
      if (result.success) {
        this.savedProjects = result.data || [];
        console.log('Projects loaded:', this.savedProjects.length);
      }
    },

    // Load all data (projects, placemarks, polygons)
    async loadAllData() {
      this.loadingMessage = 'Loading all data...';
      await Promise.all([
        this.loadAllProjects(),
        this.loadAllPlacemarks(),
        this.loadAllPolygons()
      ]);
      alert('Data berhasil dimuat!');
    },

    // Load specific project and show on map
    async loadProject(projectId) {
      const result = await this.apiCall(`/project/readOne.php?id_project=${projectId}`);
      if (result.success) {
        const project = result.data;
        
        // Clear current map
        this.clearAllMapData();
        
        // Load placemarks
        if (project.placemarks && project.placemarks.length > 0) {
          project.placemarks.forEach(placemark => {
            this.addMarkerToMap(placemark.lat, placemark.lng, false);
          });
        }
        
        // Load polygon
        if (project.polygon && project.polygon.length >= 3) {
          this.loadPolygonToMap(project.polygon);
        }
        
        this.currentProjectId = projectId;
        alert(`Project "${project.name}" berhasil dimuat!`);
      }
    },

    // Save current map state as project
    async saveProject() {
      // Get current user from localStorage
      const userData = localStorage.getItem('user');
      if (!userData) {
        alert('User tidak ditemukan. Silakan login ulang.');
        this.$router.push('/');
        return;
      }

      const currentUser = JSON.parse(userData);
      console.log('Current user for save:', currentUser);

      // Get project ID dari route parameter atau currentProject
      const projectId = this.$route.params.id || (this.currentProject ? this.currentProject.id_project : null);
      
      if (!projectId) {
        alert('Project ID tidak ditemukan. Silakan buat project baru dari Dashboard.');
        return;
      }

      console.log('Saving project ID:', projectId);

      // Siapkan data untuk disimpan
      let dataToSave = {
        id_project: projectId,
        name: this.currentProject ? this.currentProject.nama_project : 'Project ' + new Date().toLocaleString(),
        description: 'Project with markers and polygon data',
        placemarks: this.placemarks,
        timestamp: new Date().toISOString()
      };

      // Jika ada polygon legacy, simpan koordinatnya (backward compatibility)
      if (this.polygon) {
        const polygonCoords = this.polygon.getPath().getArray().map(coord => ({
          lat: coord.lat(),
          lng: coord.lng()
        }));
        dataToSave.polygon = polygonCoords;
        console.log('Saving legacy polygon with', polygonCoords.length, 'points');
      }

      console.log('Data to save:', dataToSave);
      console.log(`Project has ${this.polygons.length} polygons stored in database`);

      // Gunakan baseUrl yang benar dan endpoint save_project
      const result = await this.apiCall('/backend/api/project/save_project.php', 'POST', dataToSave);
      
      if (result && result.status === 'success') {
        alert(`Project berhasil disimpan!\nMarkers: ${this.placemarks.length}\nPolygons: ${this.polygons.length}\nLegacy Polygon: ${this.polygon ? 'Yes' : 'No'}`);
        
        // Optional: Update project info
        if (result.data) {
          console.log('Save result:', result.data);
        }
      } else {
        alert("Gagal menyimpan project: " + (result.message || 'Unknown error'));
      }
    },

    // Update existing project
    async updateProject(projectId, updateData) {
      const result = await this.apiCall('/backend/api/project/update.php', 'PUT', {
        id_project: projectId,
        ...updateData
      });
      
      if (result.success) {
        alert('Project berhasil diupdate!');
        await this.loadAllProjects();
      }
    },

    // Delete project
    async deleteProject(projectId) {
      if (!confirm('Yakin ingin menghapus project ini?')) return;
      
      const result = await this.apiCall('/backend/api/project/delete.php', 'DELETE', { id_project: projectId });
      
      if (result.success) {
        alert('Project berhasil dihapus!');
        await this.loadAllProjects();
        
        // If current project is deleted, clear map
        if (this.currentProjectId === projectId) {
          this.clearAllMapData();
          this.currentProjectId = null;
        }
      }
    },

    // Save individual placemark
    async savePlacemark(lat, lng, name = '', description = '', alamat = '', kelurahan = '', kecamatan = '', kota = '', provinsi = '') {
      const projectId = this.currentProjectId || this.$route.params.id;
      if (!projectId) {
        console.error("Project ID tidak ditemukan!");
        return;
      }

      const result = await this.apiCall('/backend/api/placemark/create.php', 'POST', {
        id_project: projectId,
        latitude: lat,
        longitude: lng,
        nama_placemark: name && name.trim() !== '' ? name : `Marker ${Date.now()}`,
        deskripsi: description || 'Auto generated placemark',
        alamat,
        kelurahan,
        kecamatan,
        kota,
        provinsi
      });

      if (result.success) {
        console.log('Placemark saved:', result.data);

        // Pastikan array placemarks ada
        if (!this.currentProject.placemarks) {
          this.currentProject.placemarks = [];
        }

        // Tambah langsung ke array biar sidebar update otomatis
        this.currentProject.placemarks.push({
          id_placemark: result.data.id_placemark, // ambil dari backend RETURNING
          nama_placemark: result.data.nama_placemark,
          deskripsi: result.data.deskripsi,
          latitude: result.data.latitude,
          longitude: result.data.longitude,
          alamat: result.data.alamat || '',
          rt: result.data.rt || '',
          rw: result.data.rw || '',
          kelurahan: result.data.kelurahan || '',
          kecamatan: result.data.kecamatan || '',
          kota: result.data.kota || '',
          provinsi: result.data.provinsi || ''
        });
      } else {
        console.error('Save placemark failed:', result.message);
      }
    },

    // Load all placemarks
    async loadAllPlacemarks() {
      const result = await this.apiCall('/backend/api/placemark/read.php');
      if (result.success) {
        console.log('Placemarks loaded:', result.data.length);
        // Optionally show all placemarks on map
        // result.data.forEach(placemark => {
        //   this.addMarkerToMap(placemark.lat, placemark.lng, false);
        // });
      }
    },

    // Save individual polygon
    async savePolygon(coordinates, name = '', description = '') {
      const result = await this.apiCall('/backend/api/polygon/create.php', 'POST', {
        id_project: this.currentProjectId,
        coordinate: coordinates,
        nama_polygon: name || `Polygon ${Date.now()}`,
        deskripsi: description
      });
      
      if (result.success) {
        console.log('Polygon saved:', result.data);
      } else {
        console.error('Polygon save failed:', result.message);
      }
    },

    // Load all polygons
    async loadAllPolygons() {
      const result = await this.apiCall('/backend/api/polygon/read.php');
      if (result.success) {
        console.log('Polygons loaded:', result.data.length);
      }
    },

    // Load polygons for specific project
    async loadProjectPolygons(projectId) {
      if (!projectId) {
        console.error('Project ID is required for loading polygons');
        return;
      }

      const result = await this.apiCall(`/backend/api/polygon/read.php?id_project=${projectId}`);
      if (result.success && result.data) {
        this.polygons = result.data.map(polygonData => {
          // Parse coordinate JSON string if needed
          let coordinates = [];
          try {
            coordinates = typeof polygonData.coordinate === 'string' 
              ? JSON.parse(polygonData.coordinate) 
              : polygonData.coordinate || [];
          } catch (e) {
            console.error('Error parsing polygon coordinates:', e);
            coordinates = [];
          }

          return {
            id: polygonData.id_polygon,
            nama_polygon: polygonData.nama_polygon,
            deskripsi: polygonData.deskripsi,
            coordinates: coordinates,
            area: null, // Remove area calculation for polylines
            googlePolygon: null // Will be set when rendered on map
          };
        });

        // Render all polygons on map
        this.renderAllPolygonsOnMap();
        
        console.log(`Loaded ${this.polygons.length} polygons for project ${projectId}`);
      } else {
        console.error('Failed to load project polygons:', result.message);
        this.polygons = [];
      }
    },

    // =============== MAP HELPER METHODS ===============
    
    addMarkerToMap(lat, lng, save = true) {
      if (!this.map) {
        console.error('Map not initialized');
        return;
      }

      console.log('Adding marker to map:', lat, lng);

      const greenIcon = {
        path: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z",
        fillColor: "#3BB142",
        fillOpacity: 1,
        strokeColor: "rgba(93, 215, 103, 0.25)",
        strokeWeight: 2,
        scale: 2,
        anchor: { x: 12, y: 24 }
      };
      
      const marker = new google.maps.Marker({
        position: { lat, lng },
        map: this.map,
        icon: greenIcon,
        draggable: true
      });

      this.markers.push(marker);

      if (save) {
        // Add to placemarks array for sidebar display
        this.placemarks.push({
          lat: lat,
          lng: lng,
          name: `Marker ${this.placemarks.length + 1}`,
          address: 'Loading address...',
          id: Date.now()
        });

        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: { lat, lng } }, (results, status) => {
          if (status === "OK" && results[0]) {
            const alamat = results[0].formatted_address;
            
            // Update address in placemarks array
            const placemarkIndex = this.placemarks.length - 1;
            if (this.placemarks[placemarkIndex]) {
              this.placemarks[placemarkIndex].address = alamat;
            }

            // 🔹 Pecah address_components
            const components = results[0].address_components;
            let kelurahan = '', kecamatan = '', kota = '', provinsi = '';

            components.forEach(c => {
              if (c.types.includes("administrative_area_level_4")) kelurahan = c.long_name;
              if (c.types.includes("administrative_area_level_3")) kecamatan = c.long_name;
              if (c.types.includes("administrative_area_level_2")) kota = c.long_name;
              if (c.types.includes("administrative_area_level_1")) provinsi = c.long_name;
            });

            this.savePlacemark(lat, lng, '', '', alamat, kelurahan, kecamatan, kota, provinsi);
          } else {
            this.savePlacemark(lat, lng);
          }
        });
      }

      console.log('Marker added successfully. Total markers:', this.markers.length);
    },

    loadPolygonToMap(coordinates, mode = "view") {
      if (!this.map) {
        console.error("Map not initialized");
        return;
      }

      // Hapus polyline/polygon lama dari map
      if (this.polyline) {
        this.polyline.setMap(null);
        this.polyline = null;
      }
      if (this.polygon) {
        this.polygon.setMap(null);
        this.polygon = null;
      }

      if (mode === "view") {
        // Polyline untuk VIEW
        let openPath = [...coordinates];
        if (
          openPath.length > 2 &&
          openPath[0].lat === openPath[openPath.length - 1].lat &&
          openPath[0].lng === openPath[openPath.length - 1].lng
        ) {
          openPath.pop();
        }

        this.polyline = new google.maps.Polyline({
          path: openPath,
          strokeColor: "#FF0000",
          strokeOpacity: 1.0,
          strokeWeight: 4,
          editable: false,
          draggable: false,
        });
        this.polyline.setMap(this.map);
        this.polygonPath = openPath;

        console.log("Polyline loaded (VIEW mode, no closing line).");
      } else {
        // Polygon untuk EDIT
        this.polygon = new google.maps.Polygon({
          paths: coordinates,
          strokeColor: "#FF0000",
          strokeOpacity: 1.0,
          strokeWeight: 4,
          fillColor: "transparent",
          fillOpacity: 0,
          editable: true,
          draggable: false,
          clickable: true,
        });
        this.polygon.setMap(this.map);
        this.polygonPath = coordinates;

        console.log("Polygon loaded (EDIT mode, closed shape).");
      }
    },

    clearAllMapData() {
      // Clear markers
      this.markers.forEach(marker => marker.setMap(null));
      this.markers = [];
      this.placemarks = [];
      
      // Clear polygon
      this.clearPolygon();
      
      // Clear all multiple polygons
      this.clearAllPolygonsFromMap();
      this.polygons = [];
      
      // Clear search marker
      if (this.searchMarker) {
        this.searchMarker.setMap(null);
        this.searchMarker = null;
      }
      
      console.log('All map data cleared');
    },

    // Render all polygons on map
    renderAllPolygonsOnMap() {
      if (!this.map) {
        console.error('Map not initialized');
        return;
      }

      // Clear existing polygons from map
      this.clearAllPolygonsFromMap();

      // Render each polygon
      this.polygons.forEach((polygonData, index) => {
        if (polygonData.coordinates && polygonData.coordinates.length >= 3) {
          this.renderSinglePolygonOnMap(polygonData, index);
        }
      });

      // Adjust map view to show all polygons
      this.fitMapToAllPolygons();
    },

    // Render single polygon on map (as open polyline without fill)
    renderSinglePolygonOnMap(polygonData, index) {
      if (!this.map || !polygonData.coordinates || polygonData.coordinates.length < 2) {
        return;
      }

      // Convert coordinates to Google Maps LatLng objects
      const path = polygonData.coordinates.map(coord => ({
        lat: parseFloat(coord.lat),
        lng: parseFloat(coord.lng)
      }));

      // Create polyline (open line without fill area) instead of polygon
      const polyline = new google.maps.Polyline({
        path: path,
        strokeColor: this.getPolygonColor(index),
        strokeOpacity: 0.8,
        strokeWeight: 3,
        editable: false,
        draggable: false
      });

      polyline.setMap(this.map);
      
      // Store reference to Google Maps polyline
      polygonData.googlePolygon = polyline; // Keep same property name for compatibility

      // Add click listener for polygon info
      polyline.addListener('click', (event) => {
        this.showPolygonInfo(polygonData, index, event.latLng);
      });

      console.log(`Rendered polyline ${index + 1} with ${path.length} points`);
    },

    // Get different colors for polygons
    getPolygonColor(index) {
      const colors = [
        '#FF0000',
      ];
      return colors[index % colors.length];
    },

    // Show polygon info window
    showPolygonInfo(polygonData, index, position) {
      const infoWindow = new google.maps.InfoWindow({
        content: `
          <div style="padding: 10px;">
            <h4>${polygonData.nama_polygon || `Polygon ${index + 1}`}</h4>
            <p><strong>Points:</strong> ${polygonData.coordinates.length}</p>
            ${polygonData.area ? `<p><strong>Area:</strong> ${polygonData.area}</p>` : ''}
            ${polygonData.deskripsi ? `<p><strong>Description:</strong> ${polygonData.deskripsi}</p>` : ''}
          </div>
        `,
        position: position
      });
      infoWindow.open(this.map);
    },

    // Clear all polygons from map
    clearAllPolygonsFromMap() {
      this.polygons.forEach(polygonData => {
        if (polygonData.googlePolygon) {
          polygonData.googlePolygon.setMap(null);
          polygonData.googlePolygon = null;
        }
      });
    },

    // Fit map to show all polygons
    fitMapToAllPolygons() {
      if (!this.map || this.polygons.length === 0) {
        return;
      }

      const bounds = new google.maps.LatLngBounds();
      let hasValidCoords = false;

      this.polygons.forEach(polygonData => {
        if (polygonData.coordinates && polygonData.coordinates.length > 0) {
          polygonData.coordinates.forEach(coord => {
            bounds.extend(new google.maps.LatLng(
              parseFloat(coord.lat), 
              parseFloat(coord.lng)
            ));
            hasValidCoords = true;
          });
        }
      });

      if (hasValidCoords) {
        this.map.fitBounds(bounds);
        // Prevent too much zoom for single point
        google.maps.event.addListenerOnce(this.map, 'bounds_changed', () => {
          if (this.map.getZoom() > 18) {
            this.map.setZoom(18);
          }
        });
      }
    },

    // Calculate area from coordinates
    calculatePolygonAreaFromCoords(coordinates) {
      if (!coordinates || coordinates.length < 3) {
        return null;
      }

      try {
        const path = coordinates.map(coord => 
          new google.maps.LatLng(parseFloat(coord.lat), parseFloat(coord.lng))
        );
        const area = google.maps.geometry.spherical.computeArea(path);
        return (area / 1000000).toFixed(2) + ' km²'; // Convert to km²
      } catch (error) {
        console.error('Error calculating polygon area:', error);
        return null;
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

      // Event listeners
      this.map.addListener("click", (e) => {
        const lat = e.latLng.lat();
        const lng = e.latLng.lng();

        if (this.drawingPolygon === true) {
          this.addPolygonPoint(lat, lng);
          return;
        }
        
        if (this.addingMarker === true) {
          this.addMarkerToMap(lat, lng);
          this.addingMarker = false;
          this.updateMapCursor();
          return;
        }
      });

      this.map.addListener("dblclick", (e) => {
        if (this.drawingPolygon && this.polygonPath.length >= 3) {
          e.stop();
          this.finishPolygon();
        }
      });

      this.map.addListener("rightclick", (e) => {
        if (this.drawingPolygon) {
          this.clearPolygon();
        }
      });

      console.log('Map initialized successfully');
      
      // Auto-load project data if we have a project ID
      const projectId = this.$route.params.id;
      if (projectId) {
        setTimeout(() => {
          this.loadProjectData(projectId);
        }, 500);
      }
    },

    // =============== UI METHODS ===============
    
    editProject(project) {
      const newName = prompt('Nama project baru:', project.name);
      const newDescription = prompt('Deskripsi project baru:', project.description);
      
      if (newName !== null) {
        this.updateProject(project.id, {
          name: newName,
          description: newDescription || project.description
        });
      }
    },

    importFile(event) {
      const file = event.target.files[0];
      if (!file) return;
      
      const reader = new FileReader();
      reader.onload = (e) => {
        try {
          const data = JSON.parse(e.target.result);
          
          // Validate data structure
          if (data.placemarks || data.polygon) {
            // Clear current map
            this.clearAllMapData();
            
            // Load placemarks
            if (data.placemarks) {
              data.placemarks.forEach(placemark => {
                this.addMarkerToMap(placemark.lat, placemark.lng, false);
              });
            }
            
            // Load polygon
            if (data.polygon && data.polygon.length >= 3) {
              this.loadPolygonToMap(data.polygon);
            }
            
            alert('File berhasil diimport!');
          } else {
            alert('Format file tidak valid!');
          }
        } catch (error) {
          alert('Error membaca file: ' + error.message);
        }
      };
      reader.readAsText(file);
      this.showImportDialog = false;
    },

   logout() {
      if (confirm('Yakin ingin logout?')) {
        // Clear all data
        this.clearAllMapData();
        this.savedProjects = [];
        
        // Clear storage
        localStorage.clear();
        
        alert("Logout berhasil!");
        
        // REDIRECT KE LOGIN
        this.$router.push('/login'); 
      }
    },

    // =============== ORIGINAL METHODS (Updated) ===============

    toggleAddMarker() {
      this.addingMarker = !this.addingMarker;
      this.drawingPolygon = false;
      this.updateMapCursor();
      console.log('Toggle add marker:', this.addingMarker);
    },

    togglePolygonMode() {
      if (!this.drawingPolygon) {
        this.drawingPolygon = true;
        this.addingMarker = false;
        this.clearPolygon();
        console.log('POLYGON MODE AKTIF');
      } else {
        this.drawingPolygon = false;
        console.log('POLYGON MODE NONAKTIF');
      }
    },

    updateMapCursor() {
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
      this.polygonPath.push({ lat: lat, lng: lng });
      
      const circle = new google.maps.Circle({
        center: { lat: lat, lng: lng },
        radius: 3,
        map: this.map,
        fillColor: '#FF0000',
        fillOpacity: 0.8,
        strokeColor: '#FFFFFF',
        strokeWeight: 2
      });
      
      this.polygonMarkers.push(circle);
      
      if (this.polygonPath.length >= 2) {
        this.drawLine();
      }
    },

    drawLine() {
      if (this.polyline) {
        this.polyline.setMap(null);
      }
      
      this.polyline = new google.maps.Polyline({
        path: this.polygonPath,
        strokeColor: '#FF0000',
        strokeOpacity: 1.0,
        strokeWeight: 4,
        geodesic: false
      });
      
      this.polyline.setMap(this.map);
    },

    async finishPolygon() {
      if (this.polygonPath.length < 3) {
        alert('Minimal 3 titik diperlukan untuk membuat polygon');
        return;
      }

      // Hapus garis sementara
      if (this.polyline) {
        this.polyline.setMap(null);
        this.polyline = null;
      }

      // Save polygon to backend first
      const projectId = this.$route.params.id || (this.currentProject ? this.currentProject.id_project : null);
      if (!projectId) {
        alert('Project ID tidak ditemukan');
        return;
      }

      try {
        const result = await this.apiCall('/backend/api/polygon/create.php', 'POST', {
          id_project: projectId,
          nama_polygon: `Polygon ${this.polygons.length + 1}`,
          deskripsi: 'Auto generated polygon',
          coordinate: JSON.stringify(this.polygonPath)
        });

        if (result.success) {
          // Create local polygon data
          const newPolygonData = {
            id: result.data?.id_polygon || Date.now(),
            nama_polygon: `Polygon ${this.polygons.length + 1}`,
            deskripsi: 'Auto generated polygon',
            coordinates: [...this.polygonPath],
            area: null, // Remove area calculation for polylines
            googlePolygon: null
          };

          // Add to polygons array
          this.polygons.push(newPolygonData);

          // Render the new polygon on map
          this.renderSinglePolygonOnMap(newPolygonData, this.polygons.length - 1);

          // Clean up drawing state
          this.polygonMarkers.forEach(circle => circle.setMap(null));
          this.polygonMarkers = [];
          this.polygonPath = [];
          this.drawingPolygon = false;
          this.updateMapCursor();

          alert(`Polygon berhasil dibuat dan disimpan! Total polygons: ${this.polygons.length}`);
          console.log(`Polygon created successfully. Total polygons: ${this.polygons.length}`);
        } else {
          alert('Gagal menyimpan polygon: ' + (result.message || 'Unknown error'));
        }
      } catch (error) {
        console.error('Error saving polygon:', error);
        alert('Error saving polygon');
      }
    },

    updatePolygonPath() {
      if (this.polygon) {
        this.polygonPath = this.polygon.getPath().getArray().map(coord => ({
          lat: coord.lat(),
          lng: coord.lng()
        }));
      }
    },

    clearPolygon() {
      this.polygonMarkers.forEach(item => {
        item.setMap(null);
      });
      this.polygonMarkers = [];
      
      if (this.polyline) {
        this.polyline.setMap(null);
        this.polyline = null;
      }
      
      if (this.polygon) {
        this.polygon.setMap(null);
        this.polygon = null;
      }
      
      this.polygonPath = [];
    },

    initMap() {
      this.map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: -7.983908, lng: 112.621391 },
        zoom: 13,
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true
      });

      // Event listeners
      this.map.addListener("click", (e) => {
        const lat = e.latLng.lat();
        const lng = e.latLng.lng();

        if (this.drawingPolygon === true) {
          this.addPolygonPoint(lat, lng);
          return;
        }
        
        if (this.addingMarker === true) {
          this.addMarkerToMap(lat, lng);
          this.addingMarker = false;
          this.updateMapCursor();
          return;
        }
      });

      this.map.addListener("dblclick", (e) => {
        if (this.drawingPolygon && this.polygonPath.length >= 3) {
          e.stop();
          this.finishPolygon();
        }
      });

      this.map.addListener("rightclick", (e) => {
        if (this.drawingPolygon) {
          this.clearPolygon();
        }
      });

      console.log('Map initialized');
    },

    addMarker(lat, lng) {
      // Use the new addMarkerToMap method
      this.addMarkerToMap(lat, lng);
      this.addingMarker = false;
      this.updateMapCursor();
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

          if (this.searchMarker) {
            this.searchMarker.setMap(null);
          }

          this.searchMarker = new google.maps.Marker({
            map: this.map,
            position: location,
            icon: {
              path: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z",
              fillColor: "#FF0000",
              fillOpacity: 1,
              strokeColor: "#ff0000",
              strokeWeight: 2,
              scale: 2,
              anchor: { x: 12, y: 24 }
            },
            title: results[0].formatted_address
          });

          this.$refs.searchInput.value = '';
        } else {
          alert("Lokasi tidak ditemukan: " + status);
        }
      });
    },

    // =============== SIDEBAR METHODS ===============
    
    toggleSection(section) {
      if (section === 'placemarks') {
        this.showPlacemarks = !this.showPlacemarks;
      } else if (section === 'polygons') {
        this.showPolygons = !this.showPolygons;
      }
    },

    // Focus map on specific placemark
    focusOnPlacemark(index) {
      if (this.placemarks[index] && this.map) {
        const placemark = this.placemarks[index];
        this.map.setCenter({ lat: placemark.lat, lng: placemark.lng });
        this.map.setZoom(18);
        
        // Highlight the marker temporarily if possible
        if (this.markers[index]) {
          // You could add bounce animation here
          this.markers[index].setAnimation(google.maps.Animation.BOUNCE);
          setTimeout(() => {
            if (this.markers[index]) {
              this.markers[index].setAnimation(null);
            }
          }, 2000);
        }
      }
    },

    // Edit placemark
    async editPlacemark(index) {
      if (this.placemarks[index]) {
        const placemark = this.placemarks[index];
        const newName = prompt('Edit marker name:', placemark.name || `Marker ${index + 1}`);
        
        if (newName !== null) {
          this.placemarks[index].name = newName.trim() || `Marker ${index + 1}`;
          // TODO: Update in backend if needed
        }
      }
    },

    // Delete placemark
    async deletePlacemark(index) {
      if (this.placemarks[index]) {
        const placemark = this.placemarks[index];
        if (confirm(`Delete marker "${placemark.name || `Marker ${index + 1}`}"?`)) {
          // Remove from map
          if (this.markers[index]) {
            this.markers[index].setMap(null);
            this.markers.splice(index, 1);
          }
          
          // Remove from placemarks array
          this.placemarks.splice(index, 1);
          
          // TODO: Delete from backend if needed
        }
      }
    },

    // Load project data and populate sidebar
    async loadProjectData(projectId) {
      try {
        console.log("Loading project data for ID:", projectId);
        this.currentProjectId = projectId;

        // Clear existing data
        this.clearAllMapData();

        // === Load project detail ===
        const projectResult = await this.apiCall(`/backend/api/project/readOne.php?id_project=${projectId}`);
        if (projectResult.success && projectResult.data) {
          this.currentProject = projectResult.data;
          console.log('✅ Project info loaded:', this.currentProject);

          // === Load placemarks ===
          if (this.currentProject.placemarks && this.currentProject.placemarks.length > 0) {
            // Clear existing placemarks
            this.placemarks = [];
            
            this.currentProject.placemarks.forEach(pm => {
              // Add to placemarks array for sidebar display
              this.placemarks.push({
                lat: parseFloat(pm.latitude),
                lng: parseFloat(pm.longitude),
                name: pm.nama_placemark || 'Marker',
                address: pm.alamat || '',
                id: pm.id_placemark
              });
              
              // Add marker to map
              this.addMarkerToMap(parseFloat(pm.latitude), parseFloat(pm.longitude), false);
            });
            console.log(`✅ Loaded ${this.placemarks.length} placemarks`);
          } else {
            this.placemarks = [];
            console.log('ℹ️ No placemarks found in this project');
          }

          // === Load multiple polygons from database ===
          await this.loadProjectPolygons(projectId);

        } else {
          console.error("❌ Failed to load project detail", projectResult.message);
        }
      } catch (err) {
        console.error("❌ Error loading project data:", err);
      }
    },

    async getAddressForPlacemark(index, lat, lng) {
      const geocoder = new google.maps.Geocoder();
      geocoder.geocode({ location: { lat, lng } }, (results, status) => {
        if (status === "OK" && results[0]) {
          this.placemarks[index].address = results[0].formatted_address;
        }
      });
    },

    // Focus map on specific placemark
    focusOnPlacemark(index) {
      const placemark = this.placemarks[index];
      if (placemark && this.map) {
        this.map.setCenter({ lat: placemark.lat, lng: placemark.lng });
        this.map.setZoom(18);
        
        // Animate marker
        if (this.markers[index]) {
          this.markers[index].setAnimation(google.maps.Animation.BOUNCE);
          setTimeout(() => {
            this.markers[index].setAnimation(null);
          }, 1500);
        }
      }
    },

    // Focus map on polygon (supports both old single polygon and new multiple polygons)
    focusOnPolygon(index = null) {
      if (index !== null && this.polygons[index]) {
        // Focus on specific polygon from multiple polygons
        const polygonData = this.polygons[index];
        if (polygonData.coordinates && polygonData.coordinates.length > 0) {
          const bounds = new google.maps.LatLngBounds();
          polygonData.coordinates.forEach(coord => {
            bounds.extend(new google.maps.LatLng(
              parseFloat(coord.lat), 
              parseFloat(coord.lng)
            ));
          });
          this.map.fitBounds(bounds);
          
          // Highlight the polygon temporarily
          if (polygonData.googlePolygon) {
            const originalColor = polygonData.googlePolygon.get('fillColor');
            polygonData.googlePolygon.setOptions({ fillColor: '#FFD700', fillOpacity: 0.7 });
            setTimeout(() => {
              polygonData.googlePolygon.setOptions({ 
                fillColor: originalColor, 
                fillOpacity: 0.35 
              });
            }, 2000);
          }
        }
      } else if (this.polygon && this.map) {
        // Legacy: Focus on old single polygon
        const bounds = new google.maps.LatLngBounds();
        this.polygonPath.forEach(point => {
          bounds.extend(new google.maps.LatLng(point.lat, point.lng));
        });
        this.map.fitBounds(bounds);
      }
    },

    // Edit placemark
    async editPlacemark(index) {
      if (!this.currentProject || !this.currentProject.placemarks) {
        console.warn("⚠️ Tidak ada project/placemark yang bisa diedit");
        return;
      }

      const placemark = this.currentProject.placemarks[index];
      if (!placemark) {
        console.warn("⚠️ Placemark dengan index", index, "tidak ditemukan");
        return;
      }

      const newName = prompt(
        "Edit placemark name:",
        placemark.nama_placemark || `Placemark ${index + 1}`
      );

      if (newName && newName.trim() !== "") {
        try {
          console.log("Edit placemark data yang dikirim:", {
            id_placemark: placemark.id_placemark,
            nama_placemark: newName.trim(),
          });

          // 🔽 disini ganti fetch lamamu dengan yang ini
          const response = await fetch("http://localhost/project_map/backend/api/placemark/update.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              id_placemark: placemark.id_placemark,
              nama_placemark: newName.trim(),
            }),
          });

          const text = await response.text();
          console.log("Raw response backend:", text);

          if (text) {
            try {
              const result = JSON.parse(text);
              if (result.success) {
                placemark.nama_placemark = newName.trim(); // update local data biar sidebar langsung berubah
                console.log("✅ Placemark updated:", placemark);
              } else {
                alert("❌ Gagal update placemark: " + result.message);
              }
            } catch (err) {
              console.error("❌ Error parsing JSON:", err);
            }
          }
        } catch (err) {
          console.error("❌ Error update placemark:", err);
        }
      }
    },

    // Delete placemark
    async deletePlacemark(index) {
      if (!confirm('Delete this placemark?')) return;
      
      // Remove marker from map
      if (this.markers[index]) {
        this.markers[index].setMap(null);
        this.markers.splice(index, 1);
      }
      
      // Remove from placemarks array
      const placemark = this.placemarks[index];
      this.placemarks.splice(index, 1);
      
      // Delete from backend if has ID
      if (placemark.id) {
        await this.apiCall(`/backend/api/placemark/delete.php`, 'DELETE', { id: placemark.id });
      }
    },

    // Edit polygon
    editPolygon() {
      if (this.polygon) {
        this.polygon.setOptions({ editable: true });
        alert('Polygon is now editable. Click and drag points to modify.');
      }
    },

    // Edit polygon (supports both old single polygon and new multiple polygons)
    editPolygon(index = null) {
      if (index !== null && this.polygons[index]) {
        // Edit specific polygon from multiple polygons
        const polygonData = this.polygons[index];
        const newName = prompt('Edit polygon name:', polygonData.nama_polygon || `Polygon ${index + 1}`);
        const newDescription = prompt('Edit polygon description:', polygonData.deskripsi || '');
        
        if (newName !== null) {
          // Update local data
          this.polygons[index].nama_polygon = newName.trim() || `Polygon ${index + 1}`;
          this.polygons[index].deskripsi = newDescription || '';
          
          // TODO: Update in backend
          this.updatePolygonInBackend(polygonData.id, {
            nama_polygon: this.polygons[index].nama_polygon,
            deskripsi: this.polygons[index].deskripsi
          });
        }
      } else {
        // Legacy: Edit old single polygon
        console.log('Edit mode for legacy polygon not implemented');
      }
    },

    // Delete polygon (supports both old single polygon and new multiple polygons)
    async deletePolygon(index = null) {
      if (index !== null && this.polygons[index]) {
        // Delete specific polygon from multiple polygons
        const polygonData = this.polygons[index];
        if (!confirm(`Delete polygon "${polygonData.nama_polygon || `Polygon ${index + 1}`}"?`)) return;
        
        try {
          // Remove from backend
          const result = await this.apiCall('/backend/api/polygon/delete.php', 'DELETE', { 
            id_polygon: polygonData.id 
          });
          
          if (result.success) {
            // Remove from map
            if (polygonData.googlePolygon) {
              polygonData.googlePolygon.setMap(null);
            }
            
            // Remove from local array
            this.polygons.splice(index, 1);
            
            alert('Polygon deleted successfully!');
          } else {
            alert('Failed to delete polygon: ' + (result.message || 'Unknown error'));
          }
        } catch (error) {
          console.error('Error deleting polygon:', error);
          alert('Error deleting polygon');
        }
      } else {
        // Legacy: Delete old single polygon
        if (!confirm('Delete this polygon?')) return;
        
        this.clearPolygon();
        
        // Delete from backend
        const projectId = this.$route.params.id || (this.currentProject ? this.currentProject.id_project : null);
        if (projectId) {
          await this.apiCall(`/backend/api/polygon/delete.php`, 'DELETE', { id_project: projectId });
        }
      }
    },

    // Update polygon in backend
    async updatePolygonInBackend(polygonId, updateData) {
      try {
        const result = await this.apiCall('/backend/api/polygon/update.php', 'PUT', {
          id_polygon: polygonId,
          ...updateData
        });
        
        if (result.success) {
          console.log('Polygon updated in backend successfully');
        } else {
          console.error('Failed to update polygon in backend:', result.message);
        }
      } catch (error) {
        console.error('Error updating polygon:', error);
      }
    },

    calculatePolygonArea() {
      if (!this.polygon || this.polygon.length === 0) {
        console.warn("Tidak ada polygon untuk dihitung.");
        return;
      }

      let totalArea = 0;

      this.polygon.forEach(poly => {
        if (poly && typeof poly.getPath === "function") {
          const path = poly.getPath();
          const area = google.maps.geometry.spherical.computeArea(path);
          totalArea += area;
        }
      });

      console.log("Total area semua polygon:", totalArea, "m²");
      return totalArea;
    },
  }
};
</script>

<style>

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(135deg, #E5EEF1 0%, #CCD2DE 100%);
  min-height: 100vh;
  color: #17677E;
}

.project-container {
  display: flex;
  min-height: 100vh;
}

.logo {
  color: #CCD2DE;
  font-size: 28px;
  font-weight: bold;
  text-align: center;
  letter-spacing: 2px;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.logo .net {
  color: #CCD2DE;
}

.logo .map {
  color: #CCD2DE;
}

.logo i {
  margin-right: 8px;  
}

.project-container .main {
  flex: 1 !important;
  margin-left: 260px !important;
  display: flex !important;
  flex-direction: column !important;
  min-height: 100vh !important;
  padding-top: 70px !important;
}

.header {
  background: linear-gradient(180deg, #17677E 0%, #145a6b 100%);
  color: #E5EEF1;
  padding: 15px 30px;
  box-shadow: 0 4px 15px rgba(23, 103, 126, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: fixed;
  top: 0;
  left: 260px; 
  right: 0;
  z-index: 100;
  height: 70px;
}

.project-container .sidebar {
  width: 260px !important;
  background: linear-gradient(180deg, #17677E 0%, #145a6b 100%) !important;
  box-shadow: 4px 0 15px rgba(23, 103, 126, 0.2) !important;
  position: fixed !important;
  top: 70px !important; /* ganti 9vh dengan tinggi header */
  left: 0 !important;
  height: calc(100vh - 70px) !important; /* supaya sidebar tidak menabrak header */
  z-index: 1000 !important;
  overflow-y: auto !important;
  transition: all 0.3s ease !important;
  display: block !important;
  visibility: visible !important;
  opacity: 1 !important;
  transform: translateX(0) !important;
}

.logout-icon {
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 8px;
  transition: all 0.3s ease;
  color: #E5EEF1;
  font-size: 18px;
  background: rgba(229, 238, 241, 0.1);
}

.logout-icon:hover {
  background: rgba(229, 238, 241, 0.2);
  transform: scale(1.1);
}

.content {
  flex: 1;
  padding: 80px;
  background: #E5EEF1;
  margin: 20px;
  border-radius: 15px;
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px rgba(23, 103, 126, 0.1);
}

h1 {
  color: #17677E;
  font-size: 28px;
  font-weight: 700;
  margin: 20px;
  text-align: left;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.project-container .toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 20px;
  margin-top: -5px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 15px;
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px rgba(23, 103, 126, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 15px;
}

.toolbar-right {
  display: flex;
  gap: 15px;
}

.search-tools {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.search-tools input[type="text"] {
  padding: 10px 14px;
  border: 2px solid #CCD2DE;
  border-radius: 10px;
  width: 280px;
  font-size: 14px;
  background: rgba(255, 255, 255, 0.9);
  color: #17677E;
  transition: all 0.3s ease;
}

.search-tools input[type="text"]:focus {
  outline: none;
  border-color: #17677E;
  box-shadow: 0 0 0 3px rgba(23, 103, 126, 0.2);
  background: white;
}

.search-tools input[type="text"]::placeholder {
  color: #999;
  font-style: italic;
}

.btn-icon {
  padding: 10px;
  border: none;
  background: #FFFFFF;
  color: #17677E;
  border-radius: 10px;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(23, 103, 126, 0.1);
  min-width: 45px;
  height: 45px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #CCD2DE;
}

.btn-icon:hover {
  background: #17677E;
  color: #E5EEF1;
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(23, 103, 126, 0.3);
}

.btn-icon.active {
  background: linear-gradient(135deg, #17677E 0%, #2980b9 100%);
  color: #E5EEF1;
  box-shadow: 0 4px 10px rgba(23, 103, 126, 0.3);
  border-color: #17677E;
}

.btn-success {
  padding: 8px 6px;
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-success:hover {
  background: linear-gradient(135deg, #218838, #1ea085);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
}

.btn-danger {
  padding: 10px 8px;
  background: linear-gradient(135deg, #dc3545, #e74c3c);
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-danger:hover {
  background: linear-gradient(135deg, #c82333, #dc2626);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(220, 53, 69, 0.4);
}

.btn-secondary {
  padding: 10px 20px;
  background: #CCD2DE;
  color: #17677E;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: #b8c1cc;
  transform: translateY(-1px);
}

.instructions {
  background: linear-gradient(135deg, #17677E, #145a6b);
  color: #E5EEF1;
  padding: 15px 25px;
  border-radius: 10px;
  margin-bottom: 20px;
  box-shadow: 0 4px 15px rgba(23, 103, 126, 0.2);
  border-left: 5px solid #CCD2DE;
}

.instructions p {
  margin: 0;
  font-size: 16px;
  font-weight: 500;
}

.instructions strong {
  color: #CCD2DE;
}

.loading {
  background: rgba(23, 103, 126, 0.95);
  color: #E5EEF1;
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 20px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(23, 103, 126, 0.3);
  backdrop-filter: blur(10px);
}

.loading i {
  margin-right: 10px;
  font-size: 18px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.map-container {
  background: white;
  border-radius: 15px;
  padding: 15px;
  margin: 20px;
  margin-top: 2px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(23, 103, 126, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

#map {
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  width: 100%;
  height: 450px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(23, 103, 126, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  backdrop-filter: blur(5px);
}

.modal-content {
  background: white;
  padding: 40px;
  border-radius: 20px;
  max-width: 500px;
  width: 90%;
  box-shadow: 0 20px 50px rgba(23, 103, 126, 0.3);
  border: 3px solid #CCD2DE;
}

.modal-content h3 {
  color: #17677E;
  margin-bottom: 25px;
  font-size: 24px;
  font-weight: 700;
  text-align: center;
}

.modal-content input[type="file"] {
  width: 100%;
  padding: 15px;
  margin: 20px 0;
  border: 2px dashed #CCD2DE;
  border-radius: 10px;
  background: rgba(229, 238, 241, 0.3);
  color: #17677E;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modal-content input[type="file"]:hover {
  border-color: #17677E;
  background: rgba(229, 238, 241, 0.5);
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 25px;
}

.sidebar-menu {
  padding: 20px 0;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.placemark-list {
  max-height: 200px; /* cukup untuk sekitar 3 item */
  overflow-y: auto;
  padding-right: 5px; /* biar isi tidak ketiban scrollbar */
}

.placemark-list::-webkit-scrollbar {
  width: 6px;
}
.placemark-list::-webkit-scrollbar-thumb {
  background: #aaa;
  border-radius: 3px;
}
.placemark-list::-webkit-scrollbar-thumb:hover {
  background: #777;
}

.project-info {
  padding: 0 20px 20px 20px;
  border-bottom: 1px solid rgba(229, 238, 241, 0.2);
  margin-bottom: 20px;
}

.project-info h3 {
  color: #E5EEF1;
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 5px;
}

.project-id {
  color: #CCD2DE;
  font-size: 12px;
  opacity: 0.8;
  margin: 0;
}

.sidebar-section {
  margin-bottom: 15px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  background: rgba(229, 238, 241, 0.1);
  cursor: pointer;
  transition: all 0.3s ease;
}

.section-header:hover {
  background: rgba(229, 238, 241, 0.15);
}

.section-header h4 {
  color: #E5EEF1;
  font-size: 14px;
  font-weight: 600;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-toggle {
  background: none;
  border: none;
  color: #CCD2DE;
  font-size: 12px;
  cursor: pointer;
  padding: 4px;
  border-radius: 3px;
  transition: all 0.3s ease;
}

.btn-toggle.active {
  transform: rotate(180deg);
}

.section-content {
  max-height: 300px;
  overflow-y: auto;
}

.sidebar-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  border-bottom: 1px solid rgba(229, 238, 241, 0.1);
  transition: all 0.3s ease;
}

.sidebar-item:hover {
  background: rgba(229, 238, 241, 0.05);
}

.item-info {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.item-icon {
  width: 32px;
  height: 32px;
  background: rgba(229, 238, 241, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #E5EEF1;
  font-size: 14px;
}

.placemark-item .item-icon {
  background: rgba(76, 175, 80, 0.3);
}

.polygon-item .item-icon {
  background: rgba(255, 215, 0, 0.3);
}

.item-details h5 {
  color: #E5EEF1;
  font-size: 13px;
  font-weight: 600;
  margin: 0 0 4px 0;
}

.coordinates {
  color: #CCD2DE;
  font-size: 11px;
  margin: 0;
  font-family: monospace;
}

.address {
  color: #CCD2DE;
  font-size: 10px;
  margin: 2px 0 0 0;
  opacity: 0.8;
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.area {
  color: #CCD2DE;
  font-size: 11px;
  margin: 2px 0 0 0;
}

.item-actions {
  display: flex;
  gap: 4px;
}

.btn-action {
  width: 24px;
  height: 24px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  transition: all 0.3s ease;
}

.btn-focus {
  background: #2196F3;
  color: white;
}

.btn-focus:hover {
  background: #1976D2;
}

.btn-edit {
  background: #FF9800;
  color: white;
}

.btn-edit:hover {
  background: #F57C00;
}

.btn-delete {
  background: #F44336;
  color: white;
}

.btn-delete:hover {
  background: #D32F2F;
}

.empty-message {
  text-align: center;
  padding: 30px 20px;
  color: #CCD2DE;
  opacity: 0.7;
}

.empty-message i {
  font-size: 32px;
  margin-bottom: 10px;
  opacity: 0.5;
}

.empty-message p {
  font-size: 12px;
  margin: 0;
}

.sidebar-actions {
  margin-top: auto;
  padding: 20px;
  border-top: 1px solid rgba(229, 238, 241, 0.2);
}

/* Scrollbar for sidebar sections */
.section-content::-webkit-scrollbar {
  width: 4px;
}

.section-content::-webkit-scrollbar-track {
  background: rgba(229, 238, 241, 0.1);
}

.section-content::-webkit-scrollbar-thumb {
  background: rgba(229, 238, 241, 0.3);
  border-radius: 2px;
}

.section-content::-webkit-scrollbar-thumb:hover {
  background: rgba(229, 238, 241, 0.5);
}

.sidebar::-webkit-scrollbar {
  width: 6px;
}

.sidebar::-webkit-scrollbar-track {
  background: rgba(229, 238, 241, 0.1);
}

.sidebar::-webkit-scrollbar-thumb {
  background: rgba(229, 238, 241, 0.3);
  border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
  background: rgba(229, 238, 241, 0.5);
}

button:focus,
input:focus {
  outline: 2px solid #17677E;
  outline-offset: 2px;
}

.text-center {
  text-align: center;
}

.mt-20 {
  margin-top: 20px;
}

.mb-20 {
  margin-bottom: 20px;
}

.hidden {
  display: none;
}

.visible {
  display: block;
}

.project-container {
  animation: fadeIn 0.6s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.map-container {
  animation: slideUp 0.8s ease-out 0.3s both;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(40px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.toolbar {
  animation: slideDown 0.8s ease-out 0.1s both;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>