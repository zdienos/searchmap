<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div id="guest">
  <section class="hero is-light is-bold" style="margin: 52px 0 10px 0">
    <div class="hero-body">
      <div class="container wow slideInLeft" data-wow-duration="1s">
        <h1 class="title">
          Place | Guest Book
          <i class="fas fa-book"></i>
        </h1>
        <p class="subtitle">Find recommend Places from Guest</p>
      </div>
    </div>
  </section>

  <div id="content" style="display: none;">
    <section class="section">
      <div class="columns">
        <div
          class="column is-8 is-offset-2 wow zoomIn"
          data-wow-duration="1s"
          data-wow-delay="0.6s"
          v-if="!loading && count > 0"
        >
          <p class="title">{{ count }} Recommendations</p>
          <p class="subtitle">
            <a href="<?= base_url('guest/area'); ?>" class="button is-primary is-outlined">
              <span class="icon">
                <i class="fas fa-map"></i>
              </span>
              <span>Areas</span>
            </a>

            <a href="<?= base_url('map/all'); ?>" class="button is-link is-outlined">
              <span class="icon">
                <i class="fas fa-eye"></i>
              </span>
              <span>Show All</span>
            </a>
          </p>

          <!-- Pencarian -->
          <div class="field">
            <div class="control has-icons-right">
              <input class="input" type="text" v-model="query" placeholder="Cari disini..." @input="fetchData()">
              <span class="icon is-small is-right">
                <i class="fas fa-search"></i>
              </span>
            </div>
          </div>
          
          <!-- Jika kueri pencarian tidak ditemukan -->
          <div class="box" v-if="!found">
            <p class="title">Query "{{ query }}" Not Found</p>
          </div>

          <!-- Isi data -->
          <div class="box" v-if="found" v-for="(guest, index) in newGuests">
            <article class="media">
              <div class="media-content">
                <div class="content">
                  <p class="heading">
                    <strong>{{ guest.name }}</strong>,
                    <em>{{ guest.date | moment }}</em>
                  </p>
                  <p class="subtitle">{{ guest.place }}</p>
                </div>
              </div>

              <div class="media-right">
                <a
                  class="button is-link"
                  :href="'<?= base_url() ?>' + 'map/' + guest.id"
                >
                  <span class="icon">
                    <i class="fas fa-eye"></i>
                  </span>
                </a>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <div class="has-text-centered" v-if="!loading && count < 1">
      <p class="title">
        <i class="fas fa-frown fa-2x"></i>
      </p>
      <p class="subtitle">
        No Recomendations
      </p>
      <a class="button is-link" href="<?= base_url('map'); ?>">
        Add New Place
      </a>

      <a class="button is-link is-outlined" href="<?= base_url('guest/area'); ?>">
        Areas
      </a>
    </div>

    <!-- Loading -->
    <div class="has-text-centered" v-if="loading">
      <p class="title">
        <i class="fas fa-spinner fa-spin"></i>
      </p>
      <p class="subtitle">
        Load Data..
      </p>
    </div>
  </div>
</div>

<script>
/*
|--------------------------------------------------------------------------
| Vue.js
|--------------------------------------------------------------------------
|
| new Vue({}) -> Instance Vue.js
|
| Digunakan untuk mengawali Vue.js
| 
| el      -> Target yang akan dimanupulasi oleh Vue.js
| data    -> Data (variabel) pada Vue.js
| methods -> Menampung Method yang akan digunakan
| 
| {{}}    -> Menampilkan data (variabel)
| @click  -> Melakukan method tertentu ketika bagian tersebut diklik
|
| Untuk lebih lengkapnya, silahkan kunjungi:
| https://vuejs.org
|
*/

const guest = new Vue({
  el: '#guest',
  data: () => ({
    guests: [],
    newGuests: [],
    count: '',
    query: '',
    found: true,
    loading: false,
    visibleModalDelete: false,
  }),

  mounted() {
    this.configPusher();
    this.getData();
  },

  methods: {
    configPusher() {
      const pusher = new Pusher('cb0f6cab84cda0b83c75', {
        cluster: 'ap1',
        encrypted: true
      });

      const channel = pusher.subscribe('search-map');

      channel.bind('add-place', () => {
        this.getData();
      })
    },

    // Method untuk mengambil data tempat
    getData () {
      document.getElementById('content').style.display = 'block';
      
      this.loading = true

      axios.get('<?= base_url() ?>' + 'api/getAllPlaces')
        .then((res) => {
          this.guests = res.data.data;
          this.count = res.data.data.length;
          this.fetchData();
          this.loading = false;
        })
        .catch((err) => {
          console.log(err);
        });
    },

    // Method untuk filter pencarian berdasarkan field 'nama' dan 'place'
    fetchData() {
      this.newGuests = [];
      let query = this.query.toLowerCase();
      this.guests.map((guest) => {
        if (guest.name.toLowerCase().indexOf(query) !== -1 || guest.place.toLowerCase().indexOf(query) !== -1) {
          this.newGuests.push(guest);
        }
      })

      if (this.newGuests.length < 1) {
        this.found = false;

      } else {
        this.found = true;
      }
    }
  },

  // Filter data tertentu
  filters: {
    // Output akan diubah dengan bantuan dari Moment.js
    moment: (date) => moment(date).fromNow(),
  },
});
</script>
