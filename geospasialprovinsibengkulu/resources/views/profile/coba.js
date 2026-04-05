const opentelemetry = require('@opentelemetry/sdk-node');
const { getNodeAutoInstrumentations } = require('@opentelemetry/auto-instrumentations-node');
const { OTLPTraceExporter } = require('@opentelemetry/exporter-trace-otlp-grpc');
const { OTLPMetricExporter } = require('@opentelemetry/exporter-metrics-otlp-grpc');
const { PeriodicExportingMetricReader } = require('@opentelemetry/sdk-metrics');

// Konfigurasi endpoint menuju OpenTelemetry Collector
const OTLP_ENDPOINT = process.env.OTLP_ENDPOINT || 'http://otel-collector:4317';

const sdk = new opentelemetry.NodeSDK({
  serviceName: 'aplikasi-layanan-utama',
  // Export Traces ke OTel Collector
  traceExporter: new OTLPTraceExporter({
    url: OTLP_ENDPOINT,
  }),
  // Export Metrics ke OTel Collector
  metricReader: new PeriodicExportingMetricReader({
    exporter: new OTLPMetricExporter({
      url: OTLP_ENDPOINT,
    }),
    exportIntervalMillis: 10000,
  }),
  // Otomatis menginstrumentasi modul bawaan Node.js (HTTP, Express, PostgreSQL, dll)
  instrumentations: [getNodeAutoInstrumentations()]
});

sdk.start()
  .then(() => console.log('Tracing dan Metrics berhasil diinisialisasi'))
  .catch((error) => console.log('Error inisialisasi OpenTelemetry', error));

// Penanganan agar graceful shutdown saat pod Kubernetes dimatikan
process.on('SIGTERM', () => {
  sdk.shutdown()
    .then(() => console.log('OpenTelemetry SDK terminated'))
    .catch((error) => console.log('Error terminating OpenTelemetry SDK', error))
    .finally(() => process.exit(0));
});
