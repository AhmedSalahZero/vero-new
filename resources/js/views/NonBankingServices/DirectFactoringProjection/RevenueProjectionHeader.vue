<template>
    <div class="kt-portlet">
        <div class="kt-portlet__body">
            <div class="row factoring-revenue-projection-by-category">
                <div
                    class="col-md-12 js-remove-hidden js-parent-to-table"
                    :style="{ display: isVisible ? 'block' : 'none' }"

                >
                    <table class="table table-white repeater-class repeater">
                        <thead>
                            <tr>
                                <th
                                    class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class"
                                >
                                    <div
                                        class="d-flex align-items-center justify-content-center"
                                    >
                                        <span>Item</span>
                                    </div>
                                </th>
                                <th
                                    v-for="(year, index) in years"
                                    :key="index"
                                    class="form-label font-weight-bold text-center align-middle interval-class header-border-down"
                                >
                                    <div
                                        class="d-flex align-items-center justify-content-center"
                                    >
                                        <span>{{ year }}</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, rowIndex) in tableData"
                                :key="rowIndex"
                                :data-repeat-formatting-decimals="row.decimals"
                                data-repeater-style=""
                            >
                                <td>
                                    <div>
                                        <input
                                            :value="row.item"
                                            disabled
                                            class="form-control text-left mt-2"
                                            type="text"
                                        />
                                    </div>
                                </td>
                                <td
                                    v-for="(value, colIndex) in row.values"
                                    :key="colIndex"
                                >
                                    <div
                                        class="d-flex align-items-center justify-content-center"
                                    >
                                        <div
                                            class="form-group three-dots-parent"
                                        >
                                            <div
                                                class="input-group input-group-sm align-items-center justify-content-center flex-nowrap"
                                            >
                                                <div
                                                    class="input-hidden-parent"
                                                >
                                                    <input
                                                        v-model="
                                                            row.values[colIndex]
                                                        "
                                                        :data-number-of-decimals="
                                                            row.decimals
                                                        "
                                                        :data-column-index="
                                                            colIndex
                                                        "
                                                        :data-name="dataName"
                                                        class="form-control copy-value-to-his-input-hidden expandable-input repeat-to-right-input-formatted"
                                                        :class="row.inputClass"
                                                        type="text"
                                                        @change="
                                                            updateWidth(
                                                                $event.target
                                                            )
                                                        "
                                                        @input="
                                                            syncHiddenValue(
                                                                row,
                                                                colIndex
                                                            )
                                                        "
                                                    />
                                                    <input
                                                        v-model="
                                                            row.hiddenValues[
                                                                colIndex
                                                            ]
                                                        "
                                                        :data-number-of-decimals="
                                                            row.decimals
                                                        "
                                                        :readonly="true"
                                                        type="hidden"
                                                        :data-name="dataName"
                                                        class="repeat-to-right-input-hidden input-hidden-with-name only-greater-than-or-equal-zero-allowed"
                                                        :class="row.hiddenClass"
                                                        :name="
                                                            row.namePrefix +
                                                            '[' +
                                                            colIndex +
                                                            ']'
                                                        "
                                                    />
                                                </div>
                                                <span
                                                    class="ml-2"
                                                    :class="{
                                                        'currency-class':
                                                            row.unit === 'EGP',
                                                    }"
                                                >
                                                    {{ row.unit }}
                                                </span>
                                            </div>
                                            <i
                                                class="fa fa-ellipsis-h pull-left repeat-to-right row-repeater-icon"
                                                :data-name="dataName"
                                                :data-column-index="colIndex"
                                                title="Repeat Right"
                                                @click="
                                                    repeatToRight(row, colIndex)
                                                "
                                            ></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "FactoringRevenueProjection",
    props: {
        isVisible: {
            type: Boolean,
            default: false, // Matches initial display:none
        },
        dataName: {
            type: String,
            default: "directFactoringRevenueProjectionByCategory",
        },
    },
    data() {
        return {
            years: ["Yr-2025", "Yr-2026", "Yr-2027"],
            tableData: [
                {
                    item: "Operating Months Per Year",
                    decimals: 0,
                    values: ["10", "12", "12"],
                    hiddenValues: ["10", "12", "12"],
                    unit: "",
                    inputClass:
                        "target_repeating_amounts only-percentage-allowed size",
                    hiddenClass: "",
                    namePrefix:
                        "directFactoringRevenueProjectionByCategory[operating_months]",
                },
                {
                    item: "Growth Rate %",
                    decimals: 2,
                    values: ["0.00", "0.00", "0.00"],
                    hiddenValues: ["0", "0", "0"],
                    unit: "%",
                    inputClass:
                        "expandable-percentage-input repeat-to-right-input-formatted",
                    hiddenClass: "recalculate-gr gr-field",
                    namePrefix:
                        "directFactoringRevenueProjectionByCategory[growth_rates]",
                },
                {
                    item: "Direct Factoring Projection",
                    decimals: 0,
                    values: ["0", "0", "0"],
                    hiddenValues: ["0", "0", "0"],
                    unit: "EGP",
                    inputClass:
                        "expandable-amount-input repeat-to-right-input-formatted current-growth-rate-result-value-formatted",
                    hiddenClass:
                        "factoring-projection-amount recalculate-factoring is-percentage-total-of current-growth-rate-result-value",
                    namePrefix:
                        "directFactoringRevenueProjectionByCategory[direct_factoring_transactions_projections]",
                },
            ],
        };
    },
    methods: {
        updateWidth(input) {
            input.style.width = (input.value.length + 1) * 10 + "px";
			// this.isVisible = false ;
			// this.rate = 40 ;
			// value {0 : 200 , 1 : 300 }
			// percentage {0 : 10 , 1 : 300}
			
        },
        syncHiddenValue(row, colIndex) {
            row.hiddenValues[colIndex] = row.values[colIndex];
        },
        repeatToRight(row, colIndex) {
            const value = row.values[colIndex];
            for (let i = colIndex + 1; i < row.values.length; i++) {
                row.values[i] = value;
                row.hiddenValues[i] = value;
            }
        },
    },
};
</script>

<style scoped>
/* Bootstrap and custom styles */
.table-white {
    background-color: #fff;
}
.header-border-down {
    border-bottom: 2px solid #dee2e6;
}
.first-column-th-class {
    min-width: 150px; /* Adjust as needed */
}
.interval-class {
    min-width: 120px; /* Adjust as needed */
}
.expandable-input {
    max-width: 60px;
    min-width: 60px;
    text-align: center;
}
.ml-2 {
    margin-left: 0.5rem;
}
.currency-class {
    font-weight: bold;
}
.row-repeater-icon {
    cursor: pointer;
    margin-left: 0.5rem;
}
</style>
