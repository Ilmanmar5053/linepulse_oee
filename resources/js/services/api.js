import axios from 'axios';

const API_BASE = '/api/v1';

export const api = {
    // Auth
    login: (credentials) => axios.post(`${API_BASE}/auth/login`, credentials),
    logout: () => axios.post(`${API_BASE}/auth/logout`),
    getMe: () => axios.get(`${API_BASE}/auth/me`),

    // Dashboard & Metrics
    getOeeKpi: (params) => axios.get(`${API_BASE}/dashboard/oee-kpi`, { params }),
    getOeeTrend: (params) => axios.get(`${API_BASE}/dashboard/oee-trend`, { params }),
    getSixBigLosses: (params) => axios.get(`${API_BASE}/dashboard/six-big-losses`, { params }),
    getParetoDowntime: (params) => axios.get(`${API_BASE}/dashboard/pareto-downtime`, { params }),
    getParetoDefects: (params) => axios.get(`${API_BASE}/dashboard/pareto-defects`, { params }),
    getMachineRanking: (params) => axios.get(`${API_BASE}/dashboard/machine-ranking`, { params }),
    getLineRanking: (params) => axios.get(`${API_BASE}/dashboard/line-ranking`, { params }),
    getShiftComparison: (params) => axios.get(`${API_BASE}/dashboard/shift-comparison`, { params }),
    getTeamComparison: (params) => axios.get(`${API_BASE}/dashboard/team-comparison`, { params }),

    // Realtime & Operations
    getRealtimeStatus: (params) => axios.get(`${API_BASE}/production-monitoring/realtime-status`, { params }),
    getProductionRecords: (params) => axios.get(`${API_BASE}/production-records`, { params }),
    createProductionRecord: (data) => axios.post(`${API_BASE}/production-records`, data),
    createProductionEntry: (data) => axios.post(`${API_BASE}/production-records`, data),
    updateProductionRecord: (id, data) => axios.put(`${API_BASE}/production-records/${id}`, data),
    deleteProductionRecord: (id) => axios.delete(`${API_BASE}/production-records/${id}`),

    // Downtime & Quality
    getDowntimes: (params) => axios.get(`${API_BASE}/downtimes`, { params }),
    createDowntime: (data) => axios.post(`${API_BASE}/downtimes`, data),
    updateDowntime: (id, data) => axios.put(`${API_BASE}/downtimes/${id}`, data),
    deleteDowntime: (id) => axios.delete(`${API_BASE}/downtimes/${id}`),
    getQualityRecords: (params) => axios.get(`${API_BASE}/quality-records`, { params }),
    createQualityRecord: (data) => axios.post(`${API_BASE}/quality-records`, data),

    // User Management & RBAC
    getUsers: (params) => axios.get(`${API_BASE}/users`, { params }),
    createUser: (data) => axios.post(`${API_BASE}/users`, data),
    generateDefaultUsers: () => axios.post(`${API_BASE}/users/generate-default`),
    updateUser: (id, data) => axios.put(`${API_BASE}/users/${id}`, data),
    deleteUser: (id) => axios.delete(`${API_BASE}/users/${id}`),
    getRoles: () => axios.get(`${API_BASE}/roles`),

    // Reports & Analytics
    getShiftSummary: (params) => axios.get(`${API_BASE}/reports/shift-summary`, { params }),
    getTroubleSummary: (params) => axios.get(`${API_BASE}/reports/trouble-summary`, { params }),
    getExportUrl: (kind, type = 'csv') => `${API_BASE}/reports/export?kind=${kind}&type=${type}`,

    // Master Data
    getPlants: () => axios.get(`${API_BASE}/master/plants`),
    createPlant: (data) => axios.post(`${API_BASE}/master/plants`, data),
    updatePlant: (id, data) => axios.put(`${API_BASE}/master/plants/${id}`, data),
    deletePlant: (id) => axios.delete(`${API_BASE}/master/plants/${id}`),

    getProductionLines: () => axios.get(`${API_BASE}/master/production-lines`),
    createProductionLine: (data) => axios.post(`${API_BASE}/master/production-lines`, data),
    updateProductionLine: (id, data) => axios.put(`${API_BASE}/master/production-lines/${id}`, data),
    deleteProductionLine: (id) => axios.delete(`${API_BASE}/master/production-lines/${id}`),

    getMachines: () => axios.get(`${API_BASE}/master/machines`),
    createMachine: (data) => axios.post(`${API_BASE}/master/machines`, data),
    importMachines: (items) => axios.post(`${API_BASE}/master/machines/import`, { items }),
    updateMachine: (id, data) => axios.put(`${API_BASE}/master/machines/${id}`, data),
    deleteMachine: (id) => axios.delete(`${API_BASE}/master/machines/${id}`),

    getProducts: () => axios.get(`${API_BASE}/master/products`),
    createProduct: (data) => axios.post(`${API_BASE}/master/products`, data),
    updateProduct: (id, data) => axios.put(`${API_BASE}/master/products/${id}`, data),
    deleteProduct: (id) => axios.delete(`${API_BASE}/master/products/${id}`),

    getDowntimeReasons: () => axios.get(`${API_BASE}/master/downtime-categories`),
    createDowntimeReason: (data) => axios.post(`${API_BASE}/master/downtime-categories`, data),
    updateDowntimeReason: (id, data) => axios.put(`${API_BASE}/master/downtime-categories/${id}`, data),
    deleteDowntimeReason: (id) => axios.delete(`${API_BASE}/master/downtime-categories/${id}`),

    getDowntimeCategories: () => axios.get(`${API_BASE}/master/downtime-categories`),
    createDowntimeCategory: (data) => axios.post(`${API_BASE}/master/downtime-categories`, data),
    importDowntimeCategories: (data) => axios.post(`${API_BASE}/master/downtime-categories/import`, data),
    updateDowntimeCategory: (id, data) => axios.put(`${API_BASE}/master/downtime-categories/${id}`, data),
    deleteDowntimeCategory: (id) => axios.delete(`${API_BASE}/master/downtime-categories/${id}`),

    getNgSections: () => axios.get(`${API_BASE}/master/ng-sections`),
    createNgSection: (data) => axios.post(`${API_BASE}/master/ng-sections`, data),
    importNgSections: (data) => axios.post(`${API_BASE}/master/ng-sections/import`, data),
    updateNgSection: (id, data) => axios.put(`${API_BASE}/master/ng-sections/${id}`, data),
    deleteNgSection: (id) => axios.delete(`${API_BASE}/master/ng-sections/${id}`),

    getDefectCategories: () => axios.get(`${API_BASE}/master/defect-categories`),
    getDefectReasons: () => axios.get(`${API_BASE}/master/defect-reasons`),
    createDefectReason: (data) => axios.post(`${API_BASE}/master/defect-reasons`, data),
    updateCountermeasure: (data) => axios.post(`${API_BASE}/master/defect-reasons/update-countermeasure`, data),
    importDefectReasons: (data) => axios.post(`${API_BASE}/master/defect-reasons/import`, data),
    updateDefectReason: (id, data) => axios.put(`${API_BASE}/master/defect-reasons/${id}`, data),
    deleteDefectReason: (id) => axios.delete(`${API_BASE}/master/defect-reasons/${id}`),

    getShifts: () => axios.get(`${API_BASE}/master/shifts`),
    createShift: (data) => axios.post(`${API_BASE}/master/shifts`, data),
    updateShift: (id, data) => axios.put(`${API_BASE}/master/shifts/${id}`, data),
    deleteShift: (id) => axios.delete(`${API_BASE}/master/shifts/${id}`),
    getGroups: () => axios.get(`${API_BASE}/master/groups`),
    createGroup: (data) => axios.post(`${API_BASE}/master/groups`, data),
    updateGroup: (id, data) => axios.put(`${API_BASE}/master/groups/${id}`, data),
    deleteGroup: (id) => axios.delete(`${API_BASE}/master/groups/${id}`),
    getSettings: () => axios.get(`${API_BASE}/master/settings`),
    updateSetting: (key, value) => axios.post(`${API_BASE}/master/settings`, { key, value }),
    getCompanyProfile: () => axios.get(`${API_BASE}/company-profile`),
    saveCompanyProfile: (data) => axios.post(`${API_BASE}/company-profile`, data),

    // NG Detail Report API
    getNgQueue: (params) => axios.get(`${API_BASE}/ng-reports/queue`, { params }),
    getNgDetail: (id) => axios.get(`${API_BASE}/ng-reports/${id}`),
    saveNgDetail: (data) => axios.post(`${API_BASE}/ng-reports`, data),
    deleteNgDetail: (id) => axios.delete(`${API_BASE}/ng-reports/${id}`),

    // Database Management & System Maintenance
    getDatabaseInfo: () => axios.get(`${API_BASE}/system/database-info`),
    cleanTransactions: (data) => axios.post(`${API_BASE}/system/clean-transactions`, data),
    optimizeDatabase: () => axios.post(`${API_BASE}/system/optimize-database`),
    getBackupExportUrl: () => `${API_BASE}/system/backup-export`,
};
