import { createRouter, createWebHistory } from 'vue-router';
import FundList from './pages/FundList.vue';
import FundCreate from './pages/FundCreate.vue';
import FundEdit from './pages/FundEdit.vue';
import FundManagerList from './pages/FundManagerList.vue';
import CompanyList from './pages/CompanyList.vue';
import DuplicateWarnings from './pages/DuplicateWarnings.vue';

const routes = [
    { path: '/', redirect: '/funds' },
    { path: '/funds', name: 'funds.index', component: FundList },
    { path: '/funds/create', name: 'funds.create', component: FundCreate },
    { path: '/funds/:id/edit', name: 'funds.edit', component: FundEdit },
    { path: '/fund-managers', name: 'fund-managers.index', component: FundManagerList },
    { path: '/companies', name: 'companies.index', component: CompanyList },
    { path: '/duplicate-warnings', name: 'duplicate-warnings.index', component: DuplicateWarnings },
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
