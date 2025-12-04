<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-xl font-bold mb-6 text-gray-800">
      Statistik Penjualan & Rating
    </h3>

    <div v-if="loading" class="text-center py-12">
      <div
        class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"
      ></div>
      <p class="mt-2 text-gray-600">Memuat data statistik...</p>
    </div>

    <div v-else-if="error" class="p-4 bg-red-100 text-red-700 rounded-lg">
      {{ error }}
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Chart Penjualan -->
      <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
        <h4 class="font-semibold text-lg mb-4 text-center text-gray-700">
          Jumlah Penjualan per Varian
        </h4>
        <div class="relative h-64">
          <Bar
            v-if="salesChartData"
            :data="salesChartData"
            :options="chartOptions"
          />
          <p v-else class="text-center text-gray-500 mt-10">
            Tidak ada data penjualan.
          </p>
        </div>
      </div>

      <!-- Chart Rating -->
      <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
        <h4 class="font-semibold text-lg mb-4 text-center text-gray-700">
          Jumlah Rating per Varian
        </h4>
        <div class="relative h-64">
          <Bar
            v-if="ratingChartData"
            :data="ratingChartData"
            :options="chartOptions"
          />
          <p v-else class="text-center text-gray-500 mt-10">
            Tidak ada data rating.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from "chart.js";
import { Bar } from "vue-chartjs";

// Register ChartJS components
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
);

const loading = ref(false);
const error = ref("");
const stats = ref([]);

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        stepSize: 1,
      },
    },
  },
};

const salesChartData = computed(() => {
  if (!stats.value.length) return null;

  // Filter items with > 0 sales to avoid clutter, or show all? Let's show all for now.
  const labels = stats.value.map((item) => item.full_name);
  const data = stats.value.map((item) => item.sold);

  return {
    labels,
    datasets: [
      {
        label: "Terjual",
        backgroundColor: "#3B82F6", // Blue-500
        data,
      },
    ],
  };
});

const ratingChartData = computed(() => {
  if (!stats.value.length) return null;

  const labels = stats.value.map((item) => item.full_name);
  const data = stats.value.map((item) => item.rating_count);

  return {
    labels,
    datasets: [
      {
        label: "Jumlah Rating",
        backgroundColor: "#F59E0B", // Amber-500
        data,
      },
    ],
  };
});

const fetchStatistics = async () => {
  loading.value = true;
  error.value = "";
  try {
    const response = await axios.get(
      "http://127.0.0.1:8000/api/manage/product/statistics",
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("authToken")}`,
        },
      }
    );
    if (response.data.success) {
      stats.value = response.data.data;
    }
  } catch (err) {
    console.error(err);
    error.value = "Gagal memuat data statistik.";
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStatistics();
});
</script>
