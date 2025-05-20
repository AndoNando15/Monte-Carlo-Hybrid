@extends('layouts.base')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        </div>

        <!-- Content Row -->
        <div class="row">
            <!-- Total Penerbangan -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Penerbangan</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPenerbangan }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-plane fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Datang -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Datang</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalDatang }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Berangkat -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Berangkat</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBerangkat }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Penjelasan Metode -->
        <div class="card mt-4 shadow">
            <div class="card-header">
                <h5 class="mb-0">Penjelasan Metode Prediksi</h5>
            </div>
            <div class="card-body">
                <h6>1. Metode Monte Carlo</h6>
                <p>
                    Metode Monte Carlo adalah teknik simulasi berbasis probabilitas yang digunakan untuk memodelkan dan
                    menganalisis sistem kompleks dengan banyak variabel acak. Metode ini menggunakan sampling acak untuk
                    memperkirakan berbagai kemungkinan hasil berdasarkan data historis.
                </p>
                <p><strong>Langkah umum Monte Carlo:</strong></p>
                <ul>
                    <li>Hitung frekuensi kemunculan data</li>
                    <li>Hitung probabilitas dari frekuensi tersebut</li>
                    <li>Hitung probabilitas kumulatif</li>
                    <li>Buat range nilai untuk simulasi random</li>
                </ul>
                <p><strong>Rumus probabilitas:</strong></p>
                <p>
                    \( P(x) = \frac{f(x)}{N} \)
                </p>
                <p>di mana:</p>
                <ul>
                    <li>\(P(x)\) = probabilitas nilai \(x\)</li>
                    <li>\(f(x)\) = frekuensi nilai \(x\)</li>
                    <li>\(N\) = total data</li>
                </ul>

                <hr>

                <h6>2. Metode Triple Exponential Smoothing (Holt-Winters)</h6>
                <p>
                    Metode ini digunakan untuk memprediksi data deret waktu yang memiliki tren dan musiman (seasonal).
                    Terdiri dari tiga komponen yaitu level (tingkat), tren, dan musiman yang diperbarui secara iteratif.
                </p>
                <p><strong>Rumus dasar Holt-Winters Additive:</strong></p>
                <ul>
                    <li>Level: \( l_t = \alpha \left(\frac{y_t}{s_{t-m}}\right) + (1-\alpha)(l_{t-1} + b_{t-1}) \)</li>
                    <li>Trend: \( b_t = \beta (l_t - l_{t-1}) + (1-\beta) b_{t-1} \)</li>
                    <li>Seasonal: \( s_t = \gamma \left(\frac{y_t}{l_t}\right) + (1-\gamma) s_{t-m} \)</li>
                    <li>Forecast: \( \hat{y}_{t+h} = (l_t + h b_t) s_{t-m+h} \)</li>
                </ul>
                <p>di mana:</p>
                <ul>
                    <li>\(y_t\) = data aktual pada waktu \(t\)</li>
                    <li>\(l_t\) = level pada waktu \(t\)</li>
                    <li>\(b_t\) = tren pada waktu \(t\)</li>
                    <li>\(s_t\) = faktor musiman pada waktu \(t\)</li>
                    <li>\(m\) = panjang periode musiman (misal: 12 bulan)</li>
                    <li>\(\alpha, \beta, \gamma\) = parameter smoothing (0 &lt; α, β, γ &lt; 1)</li>
                    <li>\(h\) = horizon prediksi ke depan</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
