/**
 * Responsabilidad: Inicialización y renderizado de gráficos del panel de analíticas administrativas.
 * Requerimientos: REQ-ADM-02, REQ-ADM-04.
 */

document.addEventListener("DOMContentLoaded", () => {
  // Manejo del selector de período personalizado
  const selectPeriodo = document.getElementById("analytics-periodo-select");
  const customDatesWrap = document.getElementById("analytics-custom-dates");

  if (selectPeriodo && customDatesWrap) {
    const toggleCustomDates = () => {
      if (selectPeriodo.value === "custom") {
        customDatesWrap.style.display = "flex";
      } else {
        customDatesWrap.style.display = "none";
      }
    };
    selectPeriodo.addEventListener("change", toggleCustomDates);
    toggleCustomDates();
  }

  // Verificar disponibilidad de Chart.js y de los datos
  if (typeof Chart === "undefined" || !window.classiaAnalyticsData) {
    return;
  }

  const data = window.classiaAnalyticsData;

  // Configuración global de fuentes y colores de Chart.js
  Chart.defaults.font.family =
    'system-ui, -apple-system, blinkmacsystemfont, "Segoe UI", sans-serif';
  Chart.defaults.color = "#4a5568";
  Chart.defaults.borderColor = "rgba(226, 232, 240, 0.8)";

  // 1. Gráfico: Evolución de Ingresos y Contrataciones
  const ctxIngresos = document.getElementById("chart-ingresos-contrataciones");
  if (ctxIngresos) {
    new Chart(ctxIngresos, {
      type: "bar",
      data: {
        labels: data.series.labels,
        datasets: [
          {
            type: "bar",
            label: "Ingresos ($)",
            data: data.series.ingresos,
            backgroundColor: "rgba(30, 58, 95, 0.75)",
            borderColor: "rgb(30, 58, 95)",
            borderWidth: 1,
            borderRadius: 4,
            yAxisID: "yIngresos",
            order: 2,
          },
          {
            type: "line",
            label: "Contrataciones",
            data: data.series.contrataciones,
            borderColor: "#0ea5e9",
            backgroundColor: "rgba(14, 165, 233, 0.15)",
            borderWidth: 3,
            pointBackgroundColor: "#0ea5e9",
            pointRadius: 3,
            pointHoverRadius: 6,
            tension: 0.35,
            fill: false,
            yAxisID: "yContrat",
            order: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: "index",
          intersect: false,
        },
        plugins: {
          legend: {
            position: "top",
            labels: { boxWidth: 14, font: { weight: "600" } },
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                if (context.dataset.label.includes("Ingresos")) {
                  return ` Ingresos: $${Number(context.raw).toLocaleString("es-AR", { minimumFractionDigits: 2 })}`;
                }
                return ` Contrataciones: ${context.raw}`;
              },
            },
          },
        },
        scales: {
          yIngresos: {
            type: "linear",
            display: true,
            position: "left",
            title: { display: true, text: "Monto ($)" },
            ticks: {
              callback: function (val) {
                return "$" + Number(val).toLocaleString("es-AR");
              },
            },
            grid: { color: "rgba(226, 232, 240, 0.6)" },
          },
          yContrat: {
            type: "linear",
            display: true,
            position: "right",
            title: { display: true, text: "Cantidad" },
            grid: { drawOnChartArea: false },
            ticks: { stepSize: 1 },
          },
        },
      },
    });
  }

  // 2. Gráfico: Visitas vs. Contrataciones (Conversión)
  const ctxVisitas = document.getElementById("chart-visitas-conversion");
  if (ctxVisitas) {
    new Chart(ctxVisitas, {
      type: "line",
      data: {
        labels: data.series.labels,
        datasets: [
          {
            label: "Visitas",
            data: data.series.visitas,
            borderColor: "#64748b",
            backgroundColor: "rgba(100, 116, 139, 0.1)",
            borderWidth: 2,
            tension: 0.3,
            fill: true,
            pointRadius: 2,
          },
          {
            label: "Contrataciones",
            data: data.series.contrataciones,
            borderColor: "#10b981",
            backgroundColor: "rgba(16, 185, 129, 0.2)",
            borderWidth: 2.5,
            tension: 0.3,
            fill: true,
            pointRadius: 3,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: "index",
          intersect: false,
        },
        plugins: {
          legend: {
            position: "top",
            labels: { boxWidth: 14, font: { weight: "600" } },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 },
            grid: { color: "rgba(226, 232, 240, 0.6)" },
          },
        },
      },
    });
  }

  // 3. Gráfico: Distribución de Valoraciones
  const ctxValoraciones = document.getElementById(
    "chart-valoraciones-distribucion"
  );
  if (ctxValoraciones) {
    const vals = data.distribucionValoraciones;
    new Chart(ctxValoraciones, {
      type: "doughnut",
      data: {
        labels: [
          "5 estrellas",
          "4 estrellas",
          "3 estrellas",
          "2 estrellas",
          "1 estrella",
        ],
        datasets: [
          {
            data: [
              vals["5"] || 0,
              vals["4"] || 0,
              vals["3"] || 0,
              vals["2"] || 0,
              vals["1"] || 0,
            ],
            backgroundColor: [
              "#10b981",
              "#34d399",
              "#f59e0b",
              "#fb923c",
              "#ef4444",
            ],
            borderWidth: 2,
            borderColor: "#ffffff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "right",
            labels: { boxWidth: 12, font: { size: 12 } },
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const val = context.raw;
                const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                return ` ${context.label}: ${val} (${pct}%)`;
              },
            },
          },
        },
      },
    });
  }

  // 4. Gráfico: Top Servicios por Ingresos
  const ctxTopServicios = document.getElementById("chart-top-servicios");
  if (ctxTopServicios && data.topServicios && data.topServicios.length > 0) {
    const labels = data.topServicios.map((s) =>
      s.titulo.length > 25 ? s.titulo.substring(0, 23) + "…" : s.titulo
    );
    const montos = data.topServicios.map((s) => parseFloat(s.total_ingresos));

    new Chart(ctxTopServicios, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            axis: "y",
            label: "Ingresos totales ($)",
            data: montos,
            backgroundColor: [
              "#1e3a5f",
              "#0284c7",
              "#0d9488",
              "#059669",
              "#6366f1",
            ],
            borderRadius: 4,
          },
        ],
      },
      options: {
        indexAxis: "y",
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (context) {
                return ` Ingresos: $${Number(context.raw).toLocaleString("es-AR", { minimumFractionDigits: 2 })}`;
              },
            },
          },
        },
        scales: {
          x: {
            ticks: {
              callback: function (val) {
                return "$" + Number(val).toLocaleString("es-AR");
              },
            },
            grid: { color: "rgba(226, 232, 240, 0.6)" },
          },
        },
      },
    });
  }
});
