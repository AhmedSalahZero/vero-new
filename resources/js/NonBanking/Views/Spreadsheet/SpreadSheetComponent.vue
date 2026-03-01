<template>
	<div ref="univerRef" style="width: 100%; height: 100vh;" />
</template>
<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

import { LocaleType, Univer, UniverInstanceType } from '@univerjs/core'
import { defaultTheme } from '@univerjs/design'
import { UniverDocsPlugin } from '@univerjs/docs'
import { UniverDocsUIPlugin } from '@univerjs/docs-ui'
import { UniverFormulaEnginePlugin } from '@univerjs/engine-formula'
import { UniverRenderEnginePlugin } from '@univerjs/engine-render'
import { UniverSheetsPlugin } from '@univerjs/sheets'
import { UniverSheetsFormulaPlugin } from '@univerjs/sheets-formula'
import { UniverSheetsUIPlugin } from '@univerjs/sheets-ui'
import { UniverUIPlugin } from '@univerjs/ui'

import DesignEnUS from '@univerjs/design/lib/locale/en-US'
import DocsUIEnUS from '@univerjs/docs-ui/lib/locale/en-US'
import SheetsFormulaEnUS from '@univerjs/sheets-formula/lib/locale/en-US'
import SheetsUIEnUS from '@univerjs/sheets-ui/lib/locale/en-US'
import SheetsEnUS from '@univerjs/sheets/lib/locale/en-US'
import UIEnUS from '@univerjs/ui/lib/locale/en-US'

import '@univerjs/design/lib/index.css'
import '@univerjs/docs-ui/lib/index.css'
import '@univerjs/sheets-formula/lib/index.css'
import '@univerjs/sheets-ui/lib/index.css'
import '@univerjs/ui/lib/index.css'

const univerRef = ref(null)
let univer = null
let initialized = false  // ✅ منع التهيئة المزدوجة

onMounted(async () => {
	// ✅ انتظر حتى الـ DOM يكون جاهز بالكامل
	await nextTick()

	if (initialized || !univerRef.value) return
	initialized = true

	univer = new Univer({
		theme: defaultTheme,
		locale: LocaleType.EN_US,
		locales: {
			[LocaleType.EN_US]: {
				...DesignEnUS,
				...UIEnUS,
				...DocsUIEnUS,
				...SheetsEnUS,
				...SheetsUIEnUS,
				...SheetsFormulaEnUS,
			},
		},
	})

	univer.registerPlugin(UniverRenderEnginePlugin)
	univer.registerPlugin(UniverFormulaEnginePlugin)
	univer.registerPlugin(UniverUIPlugin, {
		container: univerRef.value,
	})
	univer.registerPlugin(UniverDocsPlugin, { hasScroll: false })
	univer.registerPlugin(UniverDocsUIPlugin)
	univer.registerPlugin(UniverSheetsPlugin)
	univer.registerPlugin(UniverSheetsUIPlugin)
	univer.registerPlugin(UniverSheetsFormulaPlugin)

	// ✅ IDs فريدة للـ workbook والـ sheets
	const unitId = `workbook-${Date.now()}`

	univer.createUnit(UniverInstanceType.UNIVER_SHEET, {
		id: 'my-workbook',
		name: 'My Workbook',
		sheetOrder: ['sheet-001', 'sheet-002', 'sheet-003'],
		sheets: {

			// ── Sheet 1: البيانات الأساسية ──
			'sheet-001': {
				id: 'sheet-001',
				name: 'المبيعات',
				cellData: {
					0: { 0: { v: 'المنتج' }, 1: { v: 'الكمية' }, 2: { v: 'السعر' } },
					1: { 0: { v: 'تفاح' }, 1: { v: 100 }, 2: { v: 5 } },
					2: { 0: { v: 'برتقال' }, 1: { v: 200 }, 2: { v: 3 } },
					3: { 0: { v: 'مجموع' }, 1: { f: '=SUM(B2:B3)' } },
				},
			},

			// ── Sheet 2: تجيب بيانات من Sheet1 ──
			'sheet-002': {
				id: 'sheet-002',
				name: 'الإجماليات',
				cellData: {
					0: { 0: { v: 'إجمالي الكمية' }, 1: { f: "='المبيعات'!B4" } },  // ✅ ربط مباشر
					1: { 0: { v: 'ضرب السعر' }, 1: { f: "='المبيعات'!B2 * 'المبيعات'!C2" } },
					2: { 0: { v: 'SUM من Sheet1' }, 1: { f: "=SUM('المبيعات'!C2:C3)" } },
				},
			},

			// ── Sheet 3: تجيب من Sheet1 و Sheet2 ──
			'sheet-003': {
				id: 'sheet-003',
				name: 'التقرير',
				cellData: {
					0: { 0: { v: 'الكمية' }, 1: { f: "='المبيعات'!B4" } },
					1: { 0: { v: 'الإجمالي' }, 1: { f: "='الإجماليات'!B3" } },
					2: { 0: { v: 'الفرق' }, 1: { f: "='التقرير'!B1 - 'التقرير'!B2" } }, // ربط نفس الـ sheet
				},
			},

		},
	})

	// ✅ دالة إضافة Sheet جديد
	let sheetCounter = 2
	function addNewSheet() {
		if (!workbook) return

		const sheetId = `sheet-00${sheetCounter}`
		const sheetName = `Sheet${sheetCounter}`

		// إضافة sheet جديد
		workbook.addWorksheet(
			{
				id: sheetId,
				name: sheetName,
				cellData: {
					// ✅ ربط تلقائي بالـ Sheet1
					0: { 0: { v: 'من Sheet1 A1:' }, 1: { f: "='Sheet1'!A1" } },
					1: { 0: { v: 'من Sheet1 A3:' }, 1: { f: "='Sheet1'!A3" } },
					2: { 0: { v: 'مضروب في 2:' }, 1: { f: "='Sheet1'!A3 * 2" } },
				},
			},
			sheetCounter - 1  // الترتيب
		)

		sheetCounter++
	}


})

onBeforeUnmount(() => {
	if (univer) {
		univer.dispose()
		univer = null
		initialized = false
	}
})
</script>
