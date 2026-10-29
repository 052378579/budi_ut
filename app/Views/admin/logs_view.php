<?= $this->extend('layout/admin_layout') ?>

<?= $this->section('extra_styles') ?>
<link rel="stylesheet" href="/css/admin/logs.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<main class="container content-wrapper mb-5 flex-grow-1" id="ut">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="bi bi-shield-check text-danger me-2"></i>Log Sistem</h2>
            <p class="text-secondary small mt-1">Pemantauan aktivitas keamanan dan sesi pengguna aktif.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center">
                <label class="me-2 small fw-bold text-muted">Tampilkan:</label>
                <select class="form-select form-select-sm w-auto rounded-pill shadow-sm" v-model="itemsPerPage" @change="resetPages">
                    <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
                </select>
            </div>
            <input type="text" v-model="searchQuery" @input="resetPages" class="form-control search-input" placeholder="Pencarian Cepat...">
        </div>
    </div>

    <div class="card admin-card border-0 mb-4">
        <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
            <ul class="nav nav-tabs" id="logTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="online-tab" data-bs-toggle="tab" data-bs-target="#online" type="button" role="tab" aria-controls="online" aria-selected="true" @click="activeTab = 'online'">
                        <i class="bi bi-person-check-fill me-1"></i> Online
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="session-tab" data-bs-toggle="tab" data-bs-target="#session" type="button" role="tab" aria-controls="session" aria-selected="false" @click="activeTab = 'session'">
                        <i class="bi bi-pc-display me-1"></i> Sesi Aktif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit" type="button" role="tab" aria-controls="audit" aria-selected="false" @click="activeTab = 'audit'">
                        <i class="bi bi-list-columns-reverse me-1"></i> Rekam Jejak
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content" id="logTabsContent">
                
                <div class="tab-pane fade" id="audit" role="tabpanel" aria-labelledby="audit-tab">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th width="15%">Waktu</th>
                                    <th width="20%">Pengguna</th>
                                    <th width="20%">Aksi</th>
                                    <th width="30%">Detail</th>
                                    <th width="15%">IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in paginatedAudit" :key="log.id_audit">
                                    <td class="text-secondary small fw-medium">{{ formatDateTime(log.created_at) }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ log.nama_user || 'Unknown' }}</div>
                                        <span class="badge badge-soft-secondary badge-pill mt-1 role-badge">{{ log.role || 'GUEST' }}</span>
                                    </td>
                                    <td><span class="badge badge-soft-danger badge-pill text-uppercase">{{ log.aksi }}</span></td>
                                    <td class="small">{{ log.detail_aksi }}</td>
                                    <td class="font-monospace small text-muted">
                                        <code class="ip-badge">{{ formatIP(log.ip_address) }}</code>
                                        <div class="mt-1 timestamp-text">
                                            <i class="bi bi-geo-alt-fill text-danger"></i> {{ cityCache[log.ip_address] || 'Melacak lokasi...' }}
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredAudit.length === 0">
                                    <td colspan="5" class="text-center py-5 text-muted">Data log aktivitas tidak ditemukan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade show active" id="online" role="tabpanel" aria-labelledby="online-tab">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th width="15%">Aktivitas Terakhir</th>
                                    <th width="25%">Pengguna</th>
                                    <th width="20%">IP Address</th>
                                    <th width="30%">Perangkat Akses (User Agent)</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ses in paginatedOnline" :key="ses.id_session">
                                    <td class="text-secondary small fw-medium">
                                        <i class="bi bi-clock-history me-1"></i> {{ formatDateTime(ses.last_activity) }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark d-flex align-items-center">
                                            {{ ses.nama_user }}
                                            <span class="badge bg-success ms-2" style="font-size: 0.6rem; letter-spacing: 0.5px;">Online</span>
                                        </div>
                                        <div class="small text-muted">{{ ses.no_hp }}</div>
                                    </td>
                                    <td class="font-monospace small text-dark fw-bold">
                                        <code class="ip-badge">{{ formatIP(ses.ip_address) }}</code>
                                        <div class="mt-1 fw-normal text-muted timestamp-text">
                                            <i class="bi bi-geo-alt-fill text-danger"></i> {{ cityCache[ses.ip_address] || 'Melacak lokasi...' }}
                                        </div>
                                    </td>
                                    <td class="small text-secondary user-agent-text">{{ ses.user_agent }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-action text-danger shadow-sm" title="Hentikan Sesi" @click="hentikanSesi(ses.id_session)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredOnline.length === 0">
                                    <td colspan="5" class="text-center py-5 text-muted">Tidak ada pengguna yang sedang online saat ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="session" role="tabpanel" aria-labelledby="session-tab">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th width="15%">Aktivitas Terakhir</th>
                                    <th width="25%">Pengguna</th>
                                    <th width="20%">IP Address</th>
                                    <th width="30%">Perangkat Akses (User Agent)</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ses in paginatedSession" :key="ses.id_session">
                                    <td class="text-secondary small fw-medium">
                                        <i class="bi bi-clock-history me-1"></i> {{ formatDateTime(ses.last_activity) }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark d-flex align-items-center">
                                            {{ ses.nama_user }}
                                            <span v-if="isOnline(ses.last_activity)" class="badge bg-success ms-2" style="font-size: 0.6rem; letter-spacing: 0.5px;">Online</span>
                                            <span v-else class="badge bg-secondary ms-2" style="font-size: 0.6rem; letter-spacing: 0.5px;">Offline</span>
                                        </div>
                                        <div class="small text-muted">{{ ses.no_hp }}</div>
                                    </td>
                                    <td class="font-monospace small text-dark fw-bold">
                                        <code class="ip-badge">{{ formatIP(ses.ip_address) }}</code>
                                        <div class="mt-1 fw-normal text-muted timestamp-text">
                                            <i class="bi bi-geo-alt-fill text-danger"></i> {{ cityCache[ses.ip_address] || 'Melacak lokasi...' }}
                                        </div>
                                    </td>
                                    <td class="small text-secondary user-agent-text">{{ ses.user_agent }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-action text-danger shadow-sm" title="Hentikan Sesi" @click="hentikanSesi(ses.id_session)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredSession.length === 0">
                                    <td colspan="5" class="text-center py-5 text-muted">Tidak ada sesi login pengguna yang aktif saat ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center" v-if="itemsPerPage !== 'Semua'">
            <span class="small text-muted">Total Data: <strong>{{ activeTab === 'audit' ? filteredAudit.length : (activeTab === 'online' ? filteredOnline.length : filteredSession.length) }}</strong></span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: (activeTab === 'audit' ? currentAuditPage : (activeTab === 'online' ? currentOnlinePage : currentSessionPage)) === 1 }">
                        <button class="page-link shadow-sm px-3 pagination-prev" @click="activeTab === 'audit' ? currentAuditPage-- : (activeTab === 'online' ? currentOnlinePage-- : currentSessionPage--)">Sebelumnya</button>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link text-dark shadow-sm">Hal {{ activeTab === 'audit' ? currentAuditPage : (activeTab === 'online' ? currentOnlinePage : currentSessionPage) }} dari {{ activeTab === 'audit' ? totalAuditPages : (activeTab === 'online' ? totalOnlinePages : totalSessionPages) }}</span>
                    </li>
                    <li class="page-item" :class="{ disabled: (activeTab === 'audit' ? currentAuditPage : (activeTab === 'online' ? currentOnlinePage : currentSessionPage)) === (activeTab === 'audit' ? totalAuditPages : (activeTab === 'online' ? totalOnlinePages : totalSessionPages)) || (activeTab === 'audit' ? totalAuditPages : (activeTab === 'online' ? totalOnlinePages : totalSessionPages)) === 0 }">
                        <button class="page-link shadow-sm px-3 pagination-next" @click="activeTab === 'audit' ? currentAuditPage++ : (activeTab === 'online' ? currentOnlinePage++ : currentSessionPage++)">Berikutnya</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.logsData = {
        auditList: <?= $auditTrails ?? '[]' ?>,
        sessionList: <?= $userSessions ?? '[]' ?>
    };
</script>
<script src="/js/admin/logs.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>