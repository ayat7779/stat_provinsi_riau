<?php
// --- Inisialisasi Variabel dan Filter Default ---
$sql_query = "";
$report_title = "Pilih Indikator Keuangan Daerah";
$chart_labels = []; // Untuk label sumbu X (Tahun)
$chart_datasets = []; // Untuk data grafik
$y_axis_label_for_chart = ""; // Label dinamis untuk sumbu Y

// Dapatkan tahun unik yang tersedia di database
$tahun_options = [];
$result_tahun = $conn->query("SELECT DISTINCT tahun_lkpd FROM lkpd_apbd_lampiran_1 ORDER BY tahun_lkpd ASC");
if ($result_tahun) {
    while ($row_tahun = $result_tahun->fetch_assoc()) {
        $tahun_options[] = $row_tahun['tahun_lkpd'];
    }
}

// Ambil filter dari GET request atau set default
$selected_indicator_key = $_GET['indikator'] ?? '';
$selected_start_year = $_GET['start_year'] ?? (empty($tahun_options) ? date('Y') - 5 : min($tahun_options));
$selected_end_year = $_GET['end_year'] ?? (empty($tahun_options) ? date('Y') : max($tahun_options));
$selected_value_type = $_GET['value_type'] ?? 'persentase'; // 'persentase' or 'nominal'
$selected_data_type = $_GET['data_type'] ?? 'real'; // 'agr' (anggaran) or 'real' (realisasi)

// Pastikan tahun awal tidak lebih besar dari tahun akhir
if ($selected_start_year > $selected_end_year) {
    list($selected_start_year, $selected_end_year) = [$selected_end_year, $selected_start_year];
}

// --- Definisi Indikator dan Logika Query ---
$indicators = [
    "desentralisasi" => [
        "title" => "Derajat Desentralisasi = PAD / Total Pendapatan Daerah",
        "nominal_data_keys" => [
            "pad_agr" => "PAD (Anggaran)",
            "totalpendapatan_agr" => "Total Pendapatan (Anggaran)",
            "pad_real" => "PAD (Realisasi)",
            "totalpendapatan_real" => "Total Pendapatan (Realisasi)"
        ],
        "percentage_data_keys" => [
            "persentase_a" => "Anggaran (%)",
            "persentase_real" => "Realisasi (%)"
        ],
        "y_axis_label_nominal" => "Nilai (Triliun Rupiah)",
        "y_axis_label_percentage" => "Persentase (%)"
    ],
    "ketergantungan" => [
        "title" => "Ketergantungan Keuangan = Pendapatan Transfer / Total Pendapatan",
        "nominal_data_keys" => [
            "transfer_agr" => "Transfer (Anggaran)",
            "totalpendapatan_agr" => "Total Pendapatan (Anggaran)",
            "transfer_real" => "Transfer (Realisasi)",
            "totalpendapatan_real" => "Total Pendapatan (Realisasi)"
        ],
        "percentage_data_keys" => [
            "persentase_a" => "Anggaran (%)",
            "persentase_real" => "Realisasi (%)"
        ],
        "y_axis_label_nominal" => "Nilai (Triliun Rupiah)",
        "y_axis_label_percentage" => "Persentase (%)"
    ],
    "kemandirian" => [
        "title" => "Kemandirian Keuangan = PAD / (Transfer Pusat + Provinsi + Pinjaman)",
        "nominal_data_keys" => [
            "pad_agr" => "PAD (Anggaran)",
            "transfer_agr" => "Transfer (Anggaran)",
            "pad_real" => "PAD (Realisasi)",
            "transfer_real" => "Transfer (Realisasi)"
        ],
        "percentage_data_keys" => [
            "persentase_a" => "Anggaran (%)",
            "persentase_real" => "Realisasi (%)"
        ],
        "y_axis_label_nominal" => "Nilai (Triliun Rupiah)",
        "y_axis_label_percentage" => "Persentase (%)"
    ],
    "efektivitas" => [
        "title" => "Efektivitas PAD = Realisasi PAD / Target PAD",
        "nominal_data_keys" => [
            "pad_real" => "Realisasi PAD",
            "pad_agr" => "Target PAD"
        ],
        "percentage_data_keys" => [
            "persentase_efektif" => "Efektivitas PAD (%)"
        ],
        "y_axis_label_nominal" => "Nilai (Triliun Rupiah)",
        "y_axis_label_percentage" => "Persentase (%)"
    ]
];

// Start of the base SQL subquery. We'll build the SELECT part dynamically.
$base_sql_subquery_start = "
    SELECT tahun_lkpd AS tahun,
";
$base_sql_subquery_end = "
    FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id`
    WHERE tahun_lkpd BETWEEN ? AND ?
    GROUP BY tahun
";

if ($selected_indicator_key && array_key_exists($selected_indicator_key, $indicators)) {
    $current_indicator = $indicators[$selected_indicator_key];
    $report_title = $current_indicator['title'];

    $select_raw_fields_array = []; // Array to collect SELECT parts for the subquery
    $select_final_fields_array = ["tahun"]; // Array to collect SELECT parts for the main query
    $temp_chart_keys = [];


    if ($selected_value_type == 'nominal') {
        foreach ($current_indicator['nominal_data_keys'] as $key => $label) {
            $field_expression = "";
            if ($key == 'pad_agr') {
                $field_expression = "SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.1.' THEN la.jumlah_anggaran ELSE 0 END)";
            } elseif ($key == 'pad_real') {
                $field_expression = "SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.1.' THEN la.jumlah_realisasi ELSE 0 END)";
            } elseif ($key == 'totalpendapatan_agr') {
                $field_expression = "SUM(CASE WHEN LEFT(kc.kode_catatan,2)= '1.' THEN la.jumlah_anggaran ELSE 0 END)";
            } elseif ($key == 'totalpendapatan_real') {
                $field_expression = "SUM(CASE WHEN LEFT(kc.kode_catatan,2)= '1.' THEN la.jumlah_realisasi ELSE 0 END)";
            } elseif ($key == 'transfer_agr') {
                $field_expression = "(SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.2.' THEN la.jumlah_anggaran ELSE 0 END) + SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.3.' THEN la.jumlah_anggaran ELSE 0 END))";
            } elseif ($key == 'transfer_real') {
                $field_expression = "(SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.2.' THEN la.jumlah_realisasi ELSE 0 END) + SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.3.' THEN la.jumlah_realisasi ELSE 0 END))";
            }
            $select_raw_fields_array[] = "{$field_expression} AS {$key}";
        }
        $y_axis_label_for_chart = $current_indicator['y_axis_label_nominal'];

        // Determine which data to show in the chart based on value_type and data_type
        if ($selected_data_type == 'agr') {
            foreach ($current_indicator['nominal_data_keys'] as $key => $label) {
                if (strpos($key, '_agr') !== false) {
                    $temp_chart_keys[$key . '_triliun'] = $label;
                    $select_final_fields_array[] = "{$key}/1000000000000 AS {$key}_triliun";
                }
            }
        } elseif ($selected_data_type == 'real') {
            foreach ($current_indicator['nominal_data_keys'] as $key => $label) {
                if (strpos($key, '_real') !== false) {
                    $temp_chart_keys[$key . '_triliun'] = $label;
                    $select_final_fields_array[] = "{$key}/1000000000000 AS {$key}_triliun";
                }
            }
        } else { // 'both'
            foreach ($current_indicator['nominal_data_keys'] as $key => $label) {
                $temp_chart_keys[$key . '_triliun'] = $label;
                $select_final_fields_array[] = "{$key}/1000000000000 AS {$key}_triliun";
            }
        }
    } else { // $selected_value_type == 'persentase'
        // Always include raw fields needed for percentage calculation in the subquery
        if ($selected_indicator_key == 'efektivitas') {
            $select_raw_fields_array[] = "SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.1.' THEN la.jumlah_realisasi ELSE 0 END) AS pad_real";
            $select_raw_fields_array[] = "SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.1.' THEN la.jumlah_anggaran ELSE 0 END) AS pad_agr";
            $select_final_fields_array[] = "(pad_real / NULLIF(pad_agr, 0)) * 100 AS persentase_efektif"; // NULLIF to prevent division by zero
        } else {
            $select_raw_fields_array[] = "SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.1.' THEN la.jumlah_anggaran ELSE 0 END) AS pad_agr";
            $select_raw_fields_array[] = "SUM(CASE WHEN LEFT(kc.kode_catatan,2)= '1.' THEN la.jumlah_anggaran ELSE 0 END) AS totalpendapatan_agr";
            $select_raw_fields_array[] = "SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.1.' THEN la.jumlah_realisasi ELSE 0 END) AS pad_real";
            $select_raw_fields_array[] = "SUM(CASE WHEN LEFT(kc.kode_catatan,2)= '1.' THEN la.jumlah_realisasi ELSE 0 END) AS totalpendapatan_real";
            $select_raw_fields_array[] = "(SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.2.' THEN la.jumlah_anggaran ELSE 0 END) + SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.3.' THEN la.jumlah_anggaran ELSE 0 END)) AS transfer_agr";
            $select_raw_fields_array[] = "(SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.2.' THEN la.jumlah_realisasi ELSE 0 END) + SUM(CASE WHEN LEFT(kc.kode_catatan,4)= '1.3.' THEN la.jumlah_realisasi ELSE 0 END)) AS transfer_real";

            // Add percentage calculations to select_final_fields
            if ($selected_indicator_key == 'desentralisasi' || $selected_indicator_key == 'ketergantungan') {
                $select_final_fields_array[] = "(pad_agr / NULLIF(totalpendapatan_agr, 0)) * 100 AS persentase_a";
                $select_final_fields_array[] = "(pad_real / NULLIF(totalpendapatan_real, 0)) * 100 AS persentase_real";
            } elseif ($selected_indicator_key == 'kemandirian') {
                $select_final_fields_array[] = "(pad_agr / NULLIF(transfer_agr, 0)) * 100 AS persentase_a";
                $select_final_fields_array[] = "(pad_real / NULLIF(transfer_real, 0)) * 100 AS persentase_real";
            }
        }
        $y_axis_label_for_chart = $current_indicator['y_axis_label_percentage'];

        // Determine which data to show in the chart based on value_type and data_type
        if ($selected_data_type == 'agr') {
            if (isset($current_indicator['percentage_data_keys']['persentase_a'])) {
                $temp_chart_keys['persentase_a'] = $current_indicator['percentage_data_keys']['persentase_a'];
            }
        } elseif ($selected_data_type == 'real') {
            if (isset($current_indicator['percentage_data_keys']['persentase_real'])) {
                $temp_chart_keys['persentase_real'] = $current_indicator['percentage_data_keys']['persentase_real'];
            } elseif (isset($current_indicator['percentage_data_keys']['persentase_efektif'])) {
                $temp_chart_keys['persentase_efektif'] = $current_indicator['percentage_data_keys']['persentase_efektif'];
            }
        } else { // 'both'
            foreach ($current_indicator['percentage_data_keys'] as $key => $label) {
                $temp_chart_keys[$key] = $label;
            }
        }
    }

    // Join raw fields with commas
    $select_raw_fields_string = implode(", ", $select_raw_fields_array);
    $select_final_fields_string = implode(", ", $select_final_fields_array);

    // Construct the final query
    $sql_query = "SELECT {$select_final_fields_string} FROM ({$base_sql_subquery_start} {$select_raw_fields_string} {$base_sql_subquery_end}) AS a";

    // Debugging SQL Query (optional - remove or comment out in production)
    // echo "<pre>" . htmlspecialchars($sql_query) . "</pre>";
    // exit; // Stop execution to view the query

    // Run the query
    if (!empty($sql_query)) {
        $stmt = $conn->prepare($sql_query);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("ii", $selected_start_year, $selected_end_year);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $chart_data_temp = [];
            foreach ($temp_chart_keys as $key => $label) {
                $chart_data_temp[$key] = [];
            }

            while ($row = $result->fetch_assoc()) {
                $chart_labels[] = $row['tahun'];

                foreach ($temp_chart_keys as $key => $label) {
                    if (isset($row[$key])) {
                        $chart_data_temp[$key][] = round($row[$key], 2);
                    } else {
                        $chart_data_temp[$key][] = null;
                    }
                }
            }
            $stmt->close();

            $colors = [
                '#007bff',
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#6f42c1',
                '#20c997',
                '#fd7e14',
                '#e83e8c',
                '#17a2b8',
                '#6c757d'
            ];
            $color_index = 0;
            foreach ($temp_chart_keys as $key => $label) {
                $chart_datasets[] = [
                    'label' => $label,
                    'data' => $chart_data_temp[$key],
                    'borderColor' => $colors[$color_index % count($colors)],
                    'backgroundColor' => 'rgba(' . implode(',', sscanf($colors[$color_index % count($colors)], '#%02x%02x%02x')) . ', 0.1)',
                    'fill' => false,
                    'tension' => 0.1,
                    'borderWidth' => 2, // Tambahkan lebar garis
                    'pointRadius' => 4, // Tambahkan ukuran titik
                    'pointBackgroundColor' => $colors[$color_index % count($colors)],
                    'pointBorderColor' => '#fff',
                    'pointHoverRadius' => 6,
                ];
                $color_index++;
            }
        } else {
            echo "<p class='error-message'>Error menjalankan query: " . $stmt->error . "</p>";
        }
    }
}

// Tutup koneksi database
$conn->close();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        align-items: end;
        /* Align buttons/selects to the bottom */
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    select {
        padding: 10px 12px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        font-size: 1em;
        background-color: #fdfefe;
        -webkit-appearance: none;
        /* Remove default dropdown arrow for custom styling */
        -moz-appearance: none;
        appearance: none;
        background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23007bff%22%20d%3D%22M287%2069.9H5.4c-6.8%200-10.8%208.3-6.8%2014.1l140.7%20140.7c4%204%2010.5%204%2014.5%200L293.8%2084c4-5.8%200-14.1-6.8-14.1z%22%2F%3E%3C%2Fsvg%3E');
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 12px;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    select:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
    }

    .chart-container {
        width: 100%;
        height: 450px;
        /* Fixed height for better consistency */
        margin: 30px auto;
        padding: 25px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        position: relative;
        /* Needed for chart.js canvas sizing */
    }
</style>
<div class="container">
    <div class="filter-section">
        <form action="" method="GET">
            <div class="form-group">
                <label for="indikator">Pilih Indikator:</label>
                <select name="indikator" id="indikator" onchange="this.form.submit()">
                    <option value="">-- Pilih Indikator --</option>
                    <?php
                    foreach ($indicators as $key => $value) {
                        $selected = ($selected_indicator_key == $key) ? 'selected' : '';
                        echo "<option value='{$key}' {$selected}>{$value['title']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="start_year">Dari Tahun:</label>
                <select name="start_year" id="start_year" onchange="this.form.submit()">
                    <?php foreach ($tahun_options as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($selected_start_year == $year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="end_year">Sampai Tahun:</label>
                <select name="end_year" id="end_year" onchange="this.form.submit()">
                    <?php foreach ($tahun_options as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($selected_end_year == $year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="value_type">Tipe Tampilan:</label>
                <select name="value_type" id="value_type" onchange="this.form.submit()">
                    <option value="persentase" <?php echo ($selected_value_type == 'persentase') ? 'selected' : ''; ?>>Persentase</option>
                    <option value="nominal" <?php echo ($selected_value_type == 'nominal') ? 'selected' : ''; ?>>Nilai Nominal (Triliun)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="data_type">Jenis Nilai:</label>
                <select name="data_type" id="data_type" onchange="this.form.submit()">
                    <option value="real" <?php echo ($selected_data_type == 'real') ? 'selected' : ''; ?>>Realisasi</option>
                    <option value="agr" <?php echo ($selected_data_type == 'agr') ? 'selected' : ''; ?>>Anggaran</option>
                    <option value="both" <?php echo ($selected_data_type == 'both') ? 'selected' : ''; ?>>Anggaran & Realisasi</option>
                </select>
            </div>

            <input type="hidden" name="page" value="kinerja_keuangan_detail">
            <noscript><button type="submit">Terapkan Filter</button></noscript>
        </form>
    </div>

    <?php if ($selected_indicator_key && !empty($chart_datasets)): ?>
        <h2>Trend <?php echo str_replace("=", "-", $report_title); ?></h2>

        <div class="chart-container">
            <canvas id="myLineChart"></canvas>
        </div>

        <script>
            const chartLabels = <?php echo json_encode($chart_labels); ?>;
            const chartDatasets = <?php echo json_encode($chart_datasets); ?>;
            const yAxisLabel = "<?php echo $y_axis_label_for_chart; ?>";
            const valueType = "<?php echo $selected_value_type; ?>";

            const ctx = document.getElementById('myLineChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: chartDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Allows chart to fill parent container's height/width
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 14
                                },
                                color: '#343a40'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Analisis ' + '<?php echo str_replace("=", "-", $report_title); ?>',
                            font: {
                                size: 18,
                                weight: 'bold'
                            },
                            color: '#0056b3'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        if (valueType === 'persentase') {
                                            label += context.parsed.y.toFixed(2) + '%';
                                        } else {
                                            label += context.parsed.y.toFixed(3) + ' T'; // Triliun
                                        }
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: yAxisLabel,
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: '#495057'
                            },
                            ticks: {
                                callback: function(value) {
                                    if (valueType === 'persentase') {
                                        return value + '%';
                                    } else {
                                        return value.toFixed(2);
                                    }
                                },
                                font: {
                                    size: 12
                                },
                                color: '#6c757d'
                            },
                            grid: {
                                color: '#e0e0e0'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Tahun',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: '#495057'
                            },
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#6c757d'
                            },
                            grid: {
                                color: '#e0e0e0'
                            }
                        }
                    }
                }
            });
        </script>
    <?php elseif ($selected_indicator_key && empty($chart_datasets)): ?>
        <p class="no-data">Tidak ada data yang tersedia untuk indikator dengan filter yang dipilih pada rentang tahun tersebut.</p>
    <?php else: ?>
        <p class="no-data">Silakan pilih indikator dan filter untuk menampilkan grafik analisis.</p>
    <?php endif; ?>
</div>