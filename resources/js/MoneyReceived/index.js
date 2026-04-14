import { createApp } from 'vue'
import MoneyReceivedApp from './MoneyReceivedApp.vue'

const el = document.getElementById('money-received-vue-app')
if (el) {
  let initialFilterDates = {}
  let searchFieldsByTab = {}
  let advancedFilterUi = {}
  let tabTitles = {}

  const cfgNode = document.getElementById('money-received-page-config')
  if (cfgNode && cfgNode.textContent) {
    try {
      const cfg = JSON.parse(cfgNode.textContent)
      searchFieldsByTab = cfg.searchFieldsByTab || {}
      advancedFilterUi = cfg.advancedFilterUi || {}
      tabTitles = cfg.tabTitles || {}
    } catch {
      /* fall through to dataset */
    }
  }

  try {
    initialFilterDates = JSON.parse(el.dataset.initialFilterDates || '{}')
  } catch {
    initialFilterDates = {}
  }
  if (!Object.keys(searchFieldsByTab).length) {
    try {
      searchFieldsByTab = JSON.parse(el.dataset.searchFieldsByTab || '{}')
    } catch {
      searchFieldsByTab = {}
    }
  }
  if (!Object.keys(advancedFilterUi).length) {
    try {
      advancedFilterUi = JSON.parse(el.dataset.advancedFilterUi || '{}')
    } catch {
      advancedFilterUi = {}
    }
  }

  createApp(MoneyReceivedApp, {
    appLang:            el.dataset.appLang || '',
    companyId:          Number(el.dataset.companyId || 0),
    defaultActiveTab:   el.dataset.defaultActiveTab || 'cheque',
    jsonUrl:            el.dataset.jsonUrl || '',
    createUrl:          el.dataset.createUrl || '',
    createDownPaymentUrl: el.dataset.createDownPaymentUrl || '',
    canCreate:          el.dataset.canCreate === '1',
    initialFilterDates,
    searchFieldsByTab,
    advancedFilterUi,
    tabTitles,
  }).mount(el)
}
