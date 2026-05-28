/* Mikhmon — Traffic Highcharts widget */
function mikhmon_initTrafficChart() {
  var el = document.getElementById("trafficMonitor");
  if (!el) return;
  if (typeof Highcharts === "undefined") return;

  // read from dataset (works for both full render and AJAX refresh)
  var session = (el.getAttribute("data-session") || "").trim();
  var iface = (el.getAttribute("data-iface") || "").trim();
  if (!session || !iface) return;

  // make sure container has height
  if (!el.style.height) el.style.height = "320px";

  // reset any previous instance
  try { if (window.__mikhmonTrafficInterval) clearInterval(window.__mikhmonTrafficInterval); } catch (e) {}
  try { if (window.__mikhmonTrafficChart && typeof window.__mikhmonTrafficChart.destroy === "function") window.__mikhmonTrafficChart.destroy(); } catch (e) {}

  // Ensure the currently loaded Highcharts theme is applied.
  // Theme switching swaps the theme script at runtime; re-applying here keeps the chart in sync.
  try {
    if (Highcharts && Highcharts.theme) Highcharts.setOptions(Highcharts.theme);
  } catch (e) {}

  var body = document.body;
  var isDark = false;
  try { isDark = !!(body && body.classList && body.classList.contains("theme-dark")); } catch (e) {}
  var chartText = isDark ? "#f3f4f5" : "#3E3E3E";
  var grid = isDark ? "#2f353a" : "#c1c1c1";
  var bg = isDark ? "#3a4149" : "#FFFFFF";

  Highcharts.setOptions({
    global: { useUTC: false },
    chart: { height: 320 }
  });

  window.__mikhmonTrafficChart = new Highcharts.Chart({
    chart: {
      renderTo: "trafficMonitor",
      animation: Highcharts.svg,
      type: "areaspline",
      backgroundColor: bg,
      events: {
        load: function () {
          window.__mikhmonTrafficInterval = setInterval(function () {
            $.ajax({
              url: "./traffic/traffic.php?session=" + encodeURIComponent(session) + "&iface=" + encodeURIComponent(iface),
              datatype: "json",
              success: function (data) {
                var midata;
                try { midata = JSON.parse(data); } catch (e) { return; }
                if (!midata || !midata.length) return;
                var TX = parseInt(midata[0].data, 10);
                var RX = parseInt(midata[1].data, 10);
                if (isNaN(TX) || isNaN(RX)) return;
                var x = new Date().getTime();
                var c = window.__mikhmonTrafficChart;
                if (!c || !c.series || c.series.length < 2) return;
                var shift = c.series[0].data.length > 19;
                c.series[0].addPoint([x, TX], true, shift);
                c.series[1].addPoint([x, RX], true, shift);
              }
            });
          }, 8000);
        }
      }
    },
    title: { text: "Interface " + iface },
    xAxis: {
      type: "datetime",
      tickPixelInterval: 150,
      maxZoom: 20 * 1000,
      lineColor: grid,
      tickColor: grid,
      gridLineColor: grid,
      labels: { style: { color: chartText } }
    },
    yAxis: {
      minPadding: 0.2,
      maxPadding: 0.2,
      title: { text: null },
      lineColor: grid,
      tickColor: grid,
      gridLineColor: grid,
      labels: {
        style: { color: chartText },
        formatter: function () {
          var bytes = this.value;
          var sizes = ["bps", "kbps", "Mbps", "Gbps", "Tbps"];
          if (bytes === 0) return "0 bps";
          var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10);
          return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + " " + sizes[i];
        }
      }
    },
    series: [
      { name: "Tx", data: [], marker: { symbol: "circle" } },
      { name: "Rx", data: [], marker: { symbol: "circle" } }
    ],
    tooltip: {
      shared: true,
      backgroundColor: isDark ? "rgba(58, 65, 73, 0.75)" : "rgba(254, 254, 254, 0.75)",
      style: { color: chartText }
    }
  });
}
