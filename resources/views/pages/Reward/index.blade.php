<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tukar EcoPoints - Sirkula13</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSRF Token untuk AJAX Request -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-light">

    <div class="container py-5">
        <!-- Header Informasi Poin Pengguna -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm bg-success text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Halo, {{ $user->name ?? 'Warga' }}! 👋</h4>
                            <p class="mb-0 text-white-50">Kumpulkan terus EcoPoints dari aksi ramah lingkunganmu.</p>
                        </div>
                        <div class="text-end">
                            <span class="fs-5">Saldo EcoPoints:</span>
                            <h2 class="fw-bold mb-0 text-warning" id="user-points">{{ $user->points ?? 0 }} Points</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifikasi Pesan -->
        <div id="alert-container"></div>

        <!-- Daftar Rewards -->
        <h2 class="mb-4 fw-bold text-dark">Daftar Reward Tersedia</h2>
        <div class="row">
            @forelse($rewards as $reward)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        @if($reward->image)
                            <img src="{{ asset('storage/' . $reward->image) }}" class="card-img-top" alt="{{ $reward->name }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                                <span>No Image Available</span>
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold">{{ $reward->name }}</h5>
                            <p class="card-text text-muted small">{{ $reward->description }}</p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-warning text-dark fs-6">{{ $reward->points_cost }} Poin</span>
                                    <span class="text-muted small">Stok: {{ $reward->stock }}</span>
                                </div>
                                <button class="btn btn-outline-success w-100 redeem-btn"
                                        data-reward-id="{{ $reward->id }}"
                                        data-reward-cost="{{ $reward->points_cost }}">
                                    Tukar Reward
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">Belum ada reward yang tersedia saat ini.</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- JavaScript untuk menangani penukaran via fetch API -->
    <script>
        document.querySelectorAll('.redeem-btn').forEach(button => {
            button.addEventListener('click', function() {
                const rewardId = this.getAttribute('data-reward-id');
                const rewardCost = parseInt(this.getAttribute('data-reward-cost'));
                const currentPointsElement = document.getElementById('user-points');
                let currentPoints = parseInt(currentPointsElement.innerText);

                if (currentPoints < rewardCost) {
                    alert('EcoPoints kamu tidak mencukupi untuk menukar reward ini!');
                    return;
                }

                if (!confirm('Apakah kamu yakin ingin menukar reward ini?')) {
                    return;
                }

                // Kirim request POST ke backend RewardRedemptionController
                fetch('/rewards/redeem', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        user_id: {{ $user->id ?? 0 }},
                        reward_id: rewardId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    const alertContainer = document.getElementById('alert-container');
                    if (data.success) {
                        alertContainer.innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                        // Perbarui tampilan poin secara real-time
                        currentPointsElement.innerText = data.data.remaining_points + ' Points';
                        // Reload halaman setelah 2 detik agar stok terupdate
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        alertContainer.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan pada server.');
                });
            });
        });
    </script>
</body>
</html>
