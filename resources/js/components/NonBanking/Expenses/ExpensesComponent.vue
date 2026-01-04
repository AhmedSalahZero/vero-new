<template>
  <div>
    <div
      class="row"
      v-if="currentActiveTab">
      <div class="col-md-12">
        <div class="kt-portlet">
          <div class="kt-portlet__body exclude">
            <!-- start tabs -->
            <div class="form-group row justify-content-center">
              <div class="d-flex align-items-center justify-content-start mr-auto">
                <button
                  v-for="(expenseType, index) in expenseTypes"
                  :key="index"
                  @click="currentActiveTab = expenseType.id"
                  :class="[
                    'btn mb-5 type-btn btn btn-outline-info ',
                    {
                      active: currentActiveTab == expenseType.id,
                    },
                  ]">
                  {{ expenseType.title }}
                </button>
              </div>
            </div>

            <Loading :isLoading="isLoading"></Loading>

            <div v-if="!isLoading">
              <!-- start fixed monthly repeating  -->
              <div
                v-if="currentActiveTab == 'fixed_monthly_repeating_amount'"
                class="col-md-12">
                <div
                  v-for="(typeObject, typeIndex) in [
                    {
                      id: 'fixed_monthly_repeating_amount',
                      name: 'Monthly Fixed Expenses',
                    },
                  ]"
                  :key="typeIndex">
                  <div v-if="model[typeObject.id]">
                    <div
                      v-for="(item, index) in model[typeObject.id].sub_items"
                      :key="index"
                      class="row main-row-style">
                      <div class="col-md-1 max-w-trash">
                        <div
                          v-if="index > 0"
                          class="d-flex flex-column justify-content-start align-items-start">
                          <label style="visibility: hidden">Delete</label>
                          <button
                            @click="deleteRepeaterRow(index, typeObject.id)"
                            type="button"
                            class="btn btn-danger btn-md btn-danger-style ml-2"
                            title="Delete">
                            <i class="fas exclude-icon fa-trash trash-icon"></i>
                          </button>
                        </div>
                      </div>

                      <div class="col-md-2 col">
                        <Label :required="false">Expense Category</Label>
                        <Select
                          filter
                          v-model="item.expense_category"
                          :options="expenseCategories"
                          @change="updateExpenseNamePerCategories(item)"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                      </div>
                      <div class="col-md-2 col">
                        <Label :required="false">Expense Name</Label>
                        <Select
                          filter
                          v-model="item.expense_name_id"
                          :options="item.filteredExpenseNamesOptions"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                      </div>

                      <div class="col-md-1 col">
                        <Label :required="false">Start Date</Label>
                        <VueDatePicker
                          v-model="item.start_date"
                          month-picker
                          auto-apply
                          format="MMM-yyyy"
                          :min-date="new Date(studyStartDate)"
                          :start-date="new Date(studyStartDate)"
                          :max-date="
                            item.end_date ? new Date(item.end_date.year, item.end_date.month) : null
                          "></VueDatePicker>
                      </div>

                      <div class="col-md-1 col">
                        <Label :required="false">Amount</Label>
                        <InputNumber
                          v-model="item.amount"
                          :min="0"
                          :minFractionDigits="0"
                          :maxFractionDigits="2"
                          suffix=" EGP"
                          fluid />
                      </div>
                      <!-- <div class="col-md-1 col">
                        <Label :required="false">Increase Rate</Label>
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.increase_rates"
                            suffix=" %"
                            fluid />
                        </div>
                      </div> -->

                      <div class="col-md-1 col">
                        <Label :required="false">End Date</Label>
                        <VueDatePicker
                          v-model="item.end_date"
                          month-picker
                          auto-apply
                          format="MMM-yyyy"
                          :start-date="new Date(studyStartDate)"
                          :min-date="
                            item.start_date
                              ? new Date(item.start_date.year, item.start_date.month)
                              : null
                          "></VueDatePicker>
                      </div>
                      <div class="col-md-1 col">
                        <Label :required="false">Payment Terms</Label>
                        <Select
                          @change="showCustomPopup(item)"
                          filter
                          v-model="item.payment_terms"
                          :options="paymentTerms"
                          optionValue="id"
                          optionLabel="title"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                        <div
                          v-if="currentActiveCollectionModal == item"
                          @click.self="closePaymentModel()"
                          class="modal collection-modal fade show"
                          style="padding-right: 15px; display: block"
                          aria-modal="true">
                          <div
                            class="modal-dialog modal-sm modal-dialog-centered"
                            role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">Custom Payment</h5>
                                <button
                                  type="button"
                                  class="close"
                                  @click="closePaymentModel()">
                                  <span aria-hidden="true">×</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <div class="customize-elements">
                                  <table class="table exclude-table">
                                    <thead>
                                      <tr>
                                        <th class="text-center text-nowrap">Payment Rate %</th>
                                        <th class="text-center">Due In Days</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr
                                        v-for="rowIndex in [0, 1, 2, 3, 4]"
                                        :key="rowIndex">
                                        <td>
                                          <div>
                                            <InputNumber
                                              @input="handleRateChange(item, rowIndex, $event)"
                                              :placeholder="'Rate' + (rowIndex + 1)"
                                              :minFractionDigits="2"
                                              :maxFractionDigits="2"
                                              :step="0.25"
                                              :min="0"
                                              :max="100"
                                              mode="decimal"
                                              showButtons
                                              v-model="item.payment_rate[rowIndex]"
                                              suffix=" %"
                                              fluid />
                                          </div>
                                        </td>
                                        <td>
                                          <div class="">
                                            <Select
                                              filter
                                              v-model="item.due_days[rowIndex]"
                                              :options="collectionDueDays"
                                              optionValue="id"
                                              optionLabel="title"
                                              placeholder="Due Day"
                                              checkmark
                                              :highlightOnSelect="false"
                                              class="w-full md:w-56" />
                                          </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="text-center">
                                          Total:
                                          {{ calculatePaymentRatesTotal(item) }}%
                                        </td>
                                        <td></td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button
                                  type="button"
                                  class="btn btn-primary"
                                  @click="closePaymentModel()">
                                  Save
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-1 col">
                        <Label :required="false">Vat Rate</Label>
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.vat_rate"
                            suffix=" %"
                            fluid />
                        </div>
                      </div>
                      <div class="col-md-1 col">
                        <Label :required="false">Withhold Rate</Label>
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.withhold_tax_rate"
                            suffix=" %"
                            fluid />
                        </div>
                      </div>

                      <IncreaseRateModal
                        :typeObject="typeObject"
                        :item="item"
                        :index="index"
                        :increase-years-formatted="increaseYearsFormatted"
                        :modals="modals">
                        <div
                          class="col-md-1 col"
                          v-if="increaseYearsFormatted.length">
                          <Label :required="false">Increase Rate</Label>
                          <button
                            @click="modals.increaseRate.currentActive = typeObject.id + '-' + index"
                            class="btn btn-primary btn-md text-nowrap"
                            type="button">
                            Increase Rate
                          </button>
                        </div>
                      </IncreaseRateModal>

                      <!-- <div class="col-md-1">
                                <PercentageInput
                                    v-model="item.vat_rate"
                                    label="Vat Rate"
                                    placeholder="Vat Rate"
                                ></PercentageInput>
                            </div> -->
                      <!-- <div class="col-md-1">
                                <PercentageInput
                                    v-model="item.withhold_tax_rate"
                                    label="Withhold Rate"
                                    placeholder="Withhold Rate"
                                ></PercentageInput>
                            </div> -->
                    </div>
                    <div class="container mt-4">
                      <div class="row">
                        <div
                          class="col-md-6"
                          style="width: 94%">
                          <input
                            @click="addNewItem(typeObject.id)"
                            data-repeater-create=""
                            type="button"
                            class="btn btn-primary btn-sm text-white"
                            value="Add Expense" />
                        </div>
                        <!-- <div class="col-md-2 ml-auto">
                                    <button
                                        @click="
                                            openAllocationModal(typeObject.id)
                                        "
                                        class="btn btn-success"
                                        type="button"
                                    >
                                        {{ "Allocate On Revenue Streams" }}
                                    </button>
                                </div> -->
                      </div>
                      <!-- <div
                                v-if="currentModal === typeObject.id"
                                class="modal fade show d-block"
                                tabindex="-1"
                                role="dialog"
                                @click.self="closeModal"
                                style="background-color: rgba(0, 0, 0, 0.5)"
                            >
                                <div
                                    class="modal-dialog modal-dialog-centered modal-lg"
                                    role="document"
                                >
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Allocate - {{ typeObject.name }}
                                            </h5>
                                            <button
                                                type="button"
                                                class="close"
                                                @click="currentModal = null"
                                                aria-label="Close"
                                            >
                                                <span aria-hidden="true"
                                                    >&times;</span
                                                >
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <table class="table exclude-table">
                                                <tbody>
                                                    <tr>
                                                        <td
                                                            style="
                                                                padding-top: 0;
                                                                padding-bottom: 0;
                                                            "
                                                        >
                                                            <div
                                                                class="d-flex align-items-center"
                                                            >
                                                                <span
                                                                    class="mr-3 "
                                                                >
                                                                    {{
                                                                        "Allocate based on Revenue Streams"
                                                                    }}
                                                                </span>

                                                                <div
                                                                    class="kt-radio-inline"
                                                                    style="
                                                                        margin-bottom: 30px;
                                                                    "
                                                                >
                                                                    <label
                                                                        class="kt-radio kt-radio--success mb-0"
                                                                    >
                                                                        <input
                                                                            v-model="
                                                                                model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .is_as_revenue_percentages
                                                                            "
                                                                            @change="
                                                                                handleCheckboxChange(
                                                                                    typeObject.id,
                                                                                )
                                                                            "
                                                                            type="checkbox"
                                                                            class="allocate-checkbox"
                                                                        />
                                                                        <span></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr
                                                        v-for="(
                                                            product,
                                                            productIndex
                                                        ) in model[
                                                            typeObject.id
                                                        ].allocations.products"
                                                        :key="product.id"
                                                    >
                                                        <td>
                                                            <div
                                                                class="form-group"
                                                            >
                                                                <div
                                                                    class="row"
                                                                >
                                                                    <div
                                                                        class="col-9"
                                                                    >
                                                                        <label
                                                                            >Revenue
                                                                            Stream</label
                                                                        >
                                                                        <input
                                                                            :value="
                                                                                product.name
                                                                            "
                                                                            readonly
                                                                            class="form-control"
                                                                            type="text"
                                                                        />
                                                                        <input
                                                                            type="hidden"
                                                                            name="product_id"
                                                                            :value="
                                                                                product.id
                                                                            "
                                                                        />
                                                                    </div>
                                                                    <div
                                                                        class="col-3"
                                                                    >
                                                                        <label
                                                                            >Perc.%</label
                                                                        >
                                                                        <input
                                                                            v-model="
                                                                                model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .products[
                                                                                    productIndex
                                                                                ]
                                                                                    .percentage
                                                                            "
                                                                            type="text"
                                                                            class="form-control"
                                                                            :class="{
                                                                                'bg-light':
                                                                                    !model[
                                                                                        typeObject
                                                                                            .id
                                                                                    ]
                                                                                        .allocations
                                                                                        .is_as_revenue_percentages,
                                                                            }"
                                                                            :readonly="
                                                                                !model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .is_as_revenue_percentages
                                                                            "
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <div
                                                                class="form-group border-top pt-3"
                                                            >
                                                                <div
                                                                    class="row"
                                                                >
                                                                    <div
                                                                        class="col-9"
                                                                    >
                                                                        <label
                                                                            >Total</label
                                                                        >
                                                                        <input
                                                                            value="Total"
                                                                            readonly
                                                                            class="form-control"
                                                                            type="text"
                                                                        />
                                                                    </div>
                                                                    <div
                                                                        class="col-3"
                                                                    >
                                                                        <label
                                                                            >Total
                                                                            %</label
                                                                        >
                                                                        <input
                                                                            :value="
                                                                                calculateTotal(
                                                                                    typeObject.id,
                                                                                )
                                                                            "
                                                                            readonly
                                                                            class="form-control "
                                                                            :class="{
                                                                                'border-danger text-danger':
                                                                                    calculateTotal(
                                                                                        typeObject.id,
                                                                                    ) >
                                                                                    100,
                                                                                'border-success text-success':
                                                                                    calculateTotal(
                                                                                        typeObject.id,
                                                                                    ) <=
                                                                                    100,
                                                                            }"
                                                                        />
                                                                    </div>
                                                                </div>
                                                                <small
                                                                    v-if="
                                                                        calculateTotal(
                                                                            typeObject.id,
                                                                        ) > 100
                                                                    "
                                                                    class="text-danger d-block mt-2"
                                                                >
                                                                    ⚠️ Total
                                                                    percentage
                                                                    must not
                                                                    exceed 100%
                                                                </small>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="modal-footer">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                @click="currentModal = null"
                                            >
                                                Done
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                    </div>
                  </div>
                </div>
              </div>
              <!-- end fixed monthly repeating  -->

              <!-- start expense as percentage  -->
              <div
                v-if="currentActiveTab == 'percentage_of_sales'"
                class="col-md-12">
                <div
                  v-for="(typeObject, typeIndex) in [
                    {
                      id: 'percentage_of_sales',
                      name: 'Expense As Percentage',
                    },
                  ]"
                  :key="typeIndex">
                  <div v-if="model[typeObject.id]">
                    <div
                      v-for="(item, index) in model[typeObject.id].sub_items"
                      :key="index"
                      class="row flex-nowrap main-row-style">
                      <div class="max-w-trash col-md-1">
                        <Label style="visibility: hidden">ddd</Label>
                        <div
                          :style="{ visibility: index == 0 ? 'hidden' : 'visible' }"
                          class="d-flex flex-column justify-content-start align-items-start">
                          <label style="visibility: hidden">Delete</label>
                          <button
                            @click="deleteRepeaterRow(index, typeObject.id)"
                            type="button"
                            class="btn btn-danger btn-md btn-danger-style ml-2"
                            title="Delete">
                            <i class="fas exclude-icon fa-trash trash-icon"></i>
                          </button>
                        </div>
                      </div>

                      <div class="col-md-2 col">
                        <Label :required="false"
                          >Expense <br />
                          Category</Label
                        >
                        <Select
                          filter
                          v-model="item.expense_category"
                          :options="expenseCategories"
                          @change="updateExpenseNamePerCategories(item)"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                      </div>
                      <div class="col col-md-2">
                        <Label :required="false"
                          >Expense <br />
                          Name</Label
                        >
                        <Select
                          filter
                          v-model="item.expense_name_id"
                          :options="item.filteredExpenseNamesOptions"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                      </div>

                      <div class="col col-md-2">
                        <Label :required="false"
                          >Percentage <br />
                          Of</Label
                        >
                        <Select
                          filter
                          v-model="item.percentage_of"
                          :options="percentageOf"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                      </div>

                      <div class="col col-md-2">
                        <label>
                          Revenue <br />
                          Streams
                        </label>

                        <MultiSelect
                          v-model="item.revenue_stream_type"
                          showClear
                          :options="revenueStreams"
                          @change="updateRevenueCategoriesBasedOnRevenue(item)"
                          optionValue="id"
                          optionLabel="title"
                          filter
                          placeholder=""
                          :maxSelectedLabels="50"
                          class="w-full md:w-80" />
                      </div>

                      <div class="col col-md-2">
                        <label>
                          Stream <br />
                          Category
                        </label>

                        <MultiSelect
                          v-model="item.stream_category_ids"
                          showClear
                          :options="item.filteredRevenueCategoriesOptions"
                          optionValue="id"
                          optionLabel="title"
                          filter
                          placeholder=""
                          :maxSelectedLabels="50"
                          class="w-full md:w-80" />
                      </div>

                      <div class="col col-md-1 min-w-140">
                        <Label :required="false"
                          >Start <br />
                          Date</Label
                        >
                        <VueDatePicker
                          v-model="item.start_date"
                          month-picker
                          auto-apply
                          format="MMM-yyyy"
                          :min-date="new Date(studyStartDate)"
                          :start-date="new Date(studyStartDate)"
                          :max-date="
                            item.end_date ? new Date(item.end_date.year, item.end_date.month) : null
                          "></VueDatePicker>
                      </div>

                      <div class="col col-md-1 min-w-percentage">
                        <Label :required="false">Monthly <br />%</Label>
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.monthly_percentage"
                            suffix=" %"
                            fluid />
                        </div>
                      </div>

                      <div class="col col-md-1 min-w-140">
                        <Label :required="false"
                          >End <br />
                          Date</Label
                        >
                        <VueDatePicker
                          v-model="item.end_date"
                          month-picker
                          auto-apply
                          format="MMM-yyyy"
                          :start-date="new Date(studyStartDate)"
                          :min-date="
                            item.start_date
                              ? new Date(item.start_date.year, item.start_date.month)
                              : null
                          "></VueDatePicker>
                      </div>
                      <div class="col col-md-1 min-w-160">
                        <Label :required="false"
                          >Payment <br />
                          Terms</Label
                        >
                        <Select
                          @change="showCustomPopup(item)"
                          filter
                          v-model="item.payment_terms"
                          :options="paymentTerms"
                          optionValue="id"
                          optionLabel="title"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                        <div
                          v-if="currentActiveCollectionModal == item"
                          @click.self="closePaymentModel()"
                          class="modal collection-modal fade show"
                          style="padding-right: 15px; display: block"
                          aria-modal="true">
                          <div
                            class="modal-dialog modal-sm modal-dialog-centered"
                            role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">
                                  Custom <br />
                                  Payment
                                </h5>
                                <button
                                  type="button"
                                  class="close"
                                  @click="closePaymentModel()">
                                  <span aria-hidden="true">×</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <div class="customize-elements">
                                  <table class="table exclude-table">
                                    <thead>
                                      <tr>
                                        <th class="text-center text-nowrap">Payment Rate %</th>
                                        <th class="text-center">Due In Days</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr
                                        v-for="rowIndex in [0, 1, 2, 3, 4]"
                                        :key="rowIndex">
                                        <td>
                                          <div>
                                            <InputNumber
                                              @input="handleRateChange(item, rowIndex, $event)"
                                              :placeholder="'Rate' + (rowIndex + 1)"
                                              :minFractionDigits="2"
                                              :maxFractionDigits="2"
                                              :step="0.25"
                                              :min="0"
                                              :max="100"
                                              mode="decimal"
                                              showButtons
                                              v-model="item.payment_rate[rowIndex]"
                                              suffix=" %"
                                              fluid />
                                          </div>
                                        </td>
                                        <td>
                                          <div class="">
                                            <Select
                                              filter
                                              v-model="item.due_days[rowIndex]"
                                              :options="collectionDueDays"
                                              optionValue="id"
                                              optionLabel="title"
                                              placeholder="Due Day"
                                              checkmark
                                              :highlightOnSelect="false"
                                              class="w-full md:w-56" />
                                          </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="text-center">
                                          Total:
                                          {{ calculatePaymentRatesTotal(item) }}%
                                        </td>
                                        <td></td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button
                                  type="button"
                                  class="btn btn-primary"
                                  @click="closePaymentModel()">
                                  Save
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col col-md-1 min-w-percentage">
                        <Label :required="false"
                          >Vat <br />
                          Rate</Label
                        >
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.vat_rate"
                            suffix=" %"
                            fluid />
                        </div>
                      </div>
                      <div class="col col-md-1 min-w-percentage">
                        <Label :required="false"
                          >Withhold <br />
                          Rate</Label
                        >
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.withhold_tax_rate"
                            suffix=" %"
                            fluid />
                        </div>
                      </div>
                    </div>
                    <div class="container mt-4">
                      <div class="row">
                        <div
                          class="col-md-6"
                          style="width: 94%">
                          <input
                            @click="addNewItem(typeObject.id)"
                            data-repeater-create=""
                            type="button"
                            class="btn btn-primary btn-sm text-white"
                            value="Add Expense" />
                        </div>
                        <!-- <div class="col-md-2 ml-auto">
                                    <button
                                        @click="
                                            openAllocationModal(typeObject.id)
                                        "
                                        class="btn btn-success"
                                        type="button"
                                    >
                                        {{ "Allocate On Revenue Streams" }}
                                    </button>
                                </div> -->
                      </div>
                      <!-- <div
                                v-if="currentModal === typeObject.id"
                                class="modal fade show d-block"
                                tabindex="-1"
                                role="dialog"
                                @click.self="closeModal"
                                style="background-color: rgba(0, 0, 0, 0.5)"
                            >
                                <div
                                    class="modal-dialog modal-dialog-centered modal-lg"
                                    role="document"
                                >
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Allocate - {{ typeObject.name }}
                                            </h5>
                                            <button
                                                type="button"
                                                class="close"
                                                @click="currentModal = null"
                                                aria-label="Close"
                                            >
                                                <span aria-hidden="true"
                                                    >&times;</span
                                                >
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <table class="table exclude-table">
                                                <tbody>
                                                    <tr>
                                                        <td
                                                            style="
                                                                padding-top: 0;
                                                                padding-bottom: 0;
                                                            "
                                                        >
                                                            <div
                                                                class="d-flex align-items-center"
                                                            >
                                                                <span
                                                                    class="mr-3 "
                                                                >
                                                                    {{
                                                                        "Allocate based on Revenue Streams"
                                                                    }}
                                                                </span>

                                                                <div
                                                                    class="kt-radio-inline"
                                                                    style="
                                                                        margin-bottom: 30px;
                                                                    "
                                                                >
                                                                    <label
                                                                        class="kt-radio kt-radio--success mb-0"
                                                                    >
                                                                        <input
                                                                            v-model="
                                                                                model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .is_as_revenue_percentages
                                                                            "
                                                                            @change="
                                                                                handleCheckboxChange(
                                                                                    typeObject.id,
                                                                                )
                                                                            "
                                                                            type="checkbox"
                                                                            class="allocate-checkbox"
                                                                        />
                                                                        <span></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr
                                                        v-for="(
                                                            product,
                                                            productIndex
                                                        ) in model[
                                                            typeObject.id
                                                        ].allocations.products"
                                                        :key="product.id"
                                                    >
                                                        <td>
                                                            <div
                                                                class="form-group"
                                                            >
                                                                <div
                                                                    class="row"
                                                                >
                                                                    <div
                                                                        class="col-9"
                                                                    >
                                                                        <label
                                                                            >Revenue
                                                                            Stream</label
                                                                        >
                                                                        <input
                                                                            :value="
                                                                                product.name
                                                                            "
                                                                            readonly
                                                                            class="form-control"
                                                                            type="text"
                                                                        />
                                                                        <input
                                                                            type="hidden"
                                                                            name="product_id"
                                                                            :value="
                                                                                product.id
                                                                            "
                                                                        />
                                                                    </div>
                                                                    <div
                                                                        class="col-3"
                                                                    >
                                                                        <label
                                                                            >Perc.%</label
                                                                        >
                                                                        <input
                                                                            v-model="
                                                                                model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .products[
                                                                                    productIndex
                                                                                ]
                                                                                    .percentage
                                                                            "
                                                                            type="text"
                                                                            class="form-control"
                                                                            :class="{
                                                                                'bg-light':
                                                                                    !model[
                                                                                        typeObject
                                                                                            .id
                                                                                    ]
                                                                                        .allocations
                                                                                        .is_as_revenue_percentages,
                                                                            }"
                                                                            :readonly="
                                                                                !model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .is_as_revenue_percentages
                                                                            "
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <div
                                                                class="form-group border-top pt-3"
                                                            >
                                                                <div
                                                                    class="row"
                                                                >
                                                                    <div
                                                                        class="col-9"
                                                                    >
                                                                        <label
                                                                            >Total</label
                                                                        >
                                                                        <input
                                                                            value="Total"
                                                                            readonly
                                                                            class="form-control"
                                                                            type="text"
                                                                        />
                                                                    </div>
                                                                    <div
                                                                        class="col-3"
                                                                    >
                                                                        <label
                                                                            >Total
                                                                            %</label
                                                                        >
                                                                        <input
                                                                            :value="
                                                                                calculateTotal(
                                                                                    typeObject.id,
                                                                                )
                                                                            "
                                                                            readonly
                                                                            class="form-control "
                                                                            :class="{
                                                                                'border-danger text-danger':
                                                                                    calculateTotal(
                                                                                        typeObject.id,
                                                                                    ) >
                                                                                    100,
                                                                                'border-success text-success':
                                                                                    calculateTotal(
                                                                                        typeObject.id,
                                                                                    ) <=
                                                                                    100,
                                                                            }"
                                                                        />
                                                                    </div>
                                                                </div>
                                                                <small
                                                                    v-if="
                                                                        calculateTotal(
                                                                            typeObject.id,
                                                                        ) > 100
                                                                    "
                                                                    class="text-danger d-block mt-2"
                                                                >
                                                                    ⚠️ Total
                                                                    percentage
                                                                    must not
                                                                    exceed 100%
                                                                </small>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="modal-footer">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                @click="currentModal = null"
                                            >
                                                Done
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                    </div>
                  </div>
                </div>
              </div>
              <!-- end expense as percentage  -->

              <!-- start cost per units  -->
              <div
                v-if="currentActiveTab == 'cost_per_unit'"
                class="col-md-12">
                <div
                  v-for="(typeObject, typeIndex) in [
                    {
                      id: 'cost_per_unit',
                      name: 'Cost Per Contract',
                    },
                  ]"
                  :key="typeIndex">
                  <div v-if="model[typeObject.id]">
                    <div
                      v-for="(item, index) in model[typeObject.id].sub_items"
                      :key="index"
                      class="row flex-nowrap main-row-style">
                      <div class="max-w-trash col-md-1">
                        <Label style="visibility: hidden">ddd</Label>
                        <div
                          :style="{ visibility: index == 0 ? 'hidden' : 'visible' }"
                          class="d-flex flex-column justify-content-start align-items-start">
                          <label style="visibility: hidden">Delete</label>
                          <button
                            @click="deleteRepeaterRow(index, typeObject.id)"
                            type="button"
                            class="btn btn-danger btn-md btn-danger-style ml-2"
                            title="Delete">
                            <i class="fas exclude-icon fa-trash trash-icon"></i>
                          </button>
                        </div>
                      </div>

                      <div class="col-md-2 col">
                        <Label :required="false"
                          >Expense <br />
                          Category</Label
                        >
                        <Select
                          filter
                          v-model="item.expense_category"
                          :options="expenseCategories"
                          @change="updateExpenseNamePerCategories(item)"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                      </div>
                      <div class="col col-md-2">
                        <Label :required="false"
                          >Expense <br />
                          Name</Label
                        >
                        <Select
                          filter
                          v-model="item.expense_name_id"
                          :options="item.filteredExpenseNamesOptions"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                      </div>

                      <div class="col col-md-2">
                        <label>
                          Revenue <br />
                          Streams
                        </label>

                        <MultiSelect
                          v-model="item.revenue_stream_type"
                          showClear
                          :options="revenueStreams"
                          @change="updateRevenueCategoriesBasedOnRevenue(item)"
                          optionValue="id"
                          optionLabel="title"
                          filter
                          placeholder=""
                          :maxSelectedLabels="50"
                          class="w-full md:w-80" />
                      </div>

                      <div class="col col-md-2">
                        <label>
                          Stream <br />
                          Category
                        </label>

                        <MultiSelect
                          v-model="item.stream_category_ids"
                          showClear
                          :options="item.filteredRevenueCategoriesOptions"
                          optionValue="id"
                          optionLabel="title"
                          filter
                          placeholder=""
                          :maxSelectedLabels="50"
                          class="w-full md:w-80" />
                      </div>

                      <div class="col col-md-1 min-w-140">
                        <Label :required="false"
                          >Start <br />
                          Date</Label
                        >
                        <VueDatePicker
                          v-model="item.start_date"
                          month-picker
                          auto-apply
                          format="MMM-yyyy"
                          :min-date="new Date(studyStartDate)"
                          :start-date="new Date(studyStartDate)"
                          :max-date="
                            item.end_date ? new Date(item.end_date.year, item.end_date.month) : null
                          "></VueDatePicker>
                      </div>

                      <div class="col-md-1 col">
                        <Label :required="false"
                          >Cost <br />
                          Per Contract
                        </Label>
                        <InputNumber
                          v-model="item.monthly_cost_of_unit"
                          :min="0"
                          :minFractionDigits="0"
                          :maxFractionDigits="2"
                          suffix=" EGP"
                          fluid />
                      </div>

                      <div class="col col-md-1 min-w-140">
                        <Label :required="false"
                          >End <br />
                          Date</Label
                        >
                        <VueDatePicker
                          v-model="item.end_date"
                          month-picker
                          auto-apply
                          format="MMM-yyyy"
                          :start-date="new Date(studyStartDate)"
                          :min-date="
                            item.start_date
                              ? new Date(item.start_date.year, item.start_date.month)
                              : null
                          "></VueDatePicker>
                      </div>
                      <div class="col col-md-1 min-w-160">
                        <Label :required="false"
                          >Payment <br />
                          Terms</Label
                        >
                        <Select
                          @change="showCustomPopup(item)"
                          filter
                          v-model="item.payment_terms"
                          :options="paymentTerms"
                          optionValue="id"
                          optionLabel="title"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                        <div
                          v-if="currentActiveCollectionModal == item"
                          @click.self="closePaymentModel()"
                          class="modal collection-modal fade show"
                          style="padding-right: 15px; display: block"
                          aria-modal="true">
                          <div
                            class="modal-dialog modal-sm modal-dialog-centered"
                            role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">
                                  Custom <br />
                                  Payment
                                </h5>
                                <button
                                  type="button"
                                  class="close"
                                  @click="closePaymentModel()">
                                  <span aria-hidden="true">×</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <div class="customize-elements">
                                  <table class="table exclude-table">
                                    <thead>
                                      <tr>
                                        <th class="text-center text-nowrap">Payment Rate %</th>
                                        <th class="text-center">Due In Days</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr
                                        v-for="rowIndex in [0, 1, 2, 3, 4]"
                                        :key="rowIndex">
                                        <td>
                                          <div>
                                            <InputNumber
                                              @input="handleRateChange(item, rowIndex, $event)"
                                              :placeholder="'Rate' + (rowIndex + 1)"
                                              :minFractionDigits="2"
                                              :maxFractionDigits="2"
                                              :step="0.25"
                                              :min="0"
                                              :max="100"
                                              mode="decimal"
                                              showButtons
                                              v-model="item.payment_rate[rowIndex]"
                                              suffix=" %"
                                              fluid />
                                          </div>
                                        </td>
                                        <td>
                                          <div class="">
                                            <Select
                                              filter
                                              v-model="item.due_days[rowIndex]"
                                              :options="collectionDueDays"
                                              optionValue="id"
                                              optionLabel="title"
                                              placeholder="Due Day"
                                              checkmark
                                              :highlightOnSelect="false"
                                              class="w-full md:w-56" />
                                          </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="text-center">
                                          Total:
                                          {{ calculatePaymentRatesTotal(item) }}%
                                        </td>
                                        <td></td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button
                                  type="button"
                                  class="btn btn-primary"
                                  @click="closePaymentModel()">
                                  Save
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col col-md-1 min-w-percentage">
                        <Label :required="false"
                          >Vat <br />
                          Rate</Label
                        >
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.vat_rate"
                            suffix=" %"
                            fluid />
                        </div>
                      </div>
                      <div class="col col-md-1 min-w-percentage">
                        <Label :required="false"
                          >Withhold <br />
                          Rate</Label
                        >
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.withhold_tax_rate"
                            suffix=" %"
                            fluid />
                        </div>
                      </div>

                      <IncreaseRateModal
                        :typeObject="typeObject"
                        :item="item"
                        :index="index"
                        :increase-years-formatted="increaseYearsFormatted"
                        :modals="modals">
                        <div
                          class="col-md-1 col"
                          v-if="increaseYearsFormatted.length">
                          <Label :required="false"
                            >Increase <br />
                            Rate</Label
                          >
                          <button
                            @click="modals.increaseRate.currentActive = typeObject.id + '-' + index"
                            class="btn btn-primary btn-md text-nowrap"
                            type="button">
                            Increase Rate
                          </button>
                        </div>
                      </IncreaseRateModal>

                      <!-- <div class="col-md-1">
                                <PercentageInput
                                    v-model="item.vat_rate"
                                    label="Vat Rate"
                                    placeholder="Vat Rate"
                                ></PercentageInput>
                            </div> -->
                      <!-- <div class="col-md-1">
                                <PercentageInput
                                    v-model="item.withhold_tax_rate"
                                    label="Withhold Rate"
                                    placeholder="Withhold Rate"
                                ></PercentageInput>
                            </div> -->
                    </div>
                    <div class="container mt-4">
                      <div class="row">
                        <div
                          class="col-md-6"
                          style="width: 94%">
                          <input
                            @click="addNewItem(typeObject.id)"
                            data-repeater-create=""
                            type="button"
                            class="btn btn-primary btn-sm text-white"
                            value="Add Expense" />
                        </div>
                        <!-- <div class="col-md-2 ml-auto">
                                    <button
                                        @click="
                                            openAllocationModal(typeObject.id)
                                        "
                                        class="btn btn-success"
                                        type="button"
                                    >
                                        {{ "Allocate On Revenue Streams" }}
                                    </button>
                                </div> -->
                      </div>
                      <!-- <div
                                v-if="currentModal === typeObject.id"
                                class="modal fade show d-block"
                                tabindex="-1"
                                role="dialog"
                                @click.self="closeModal"
                                style="background-color: rgba(0, 0, 0, 0.5)"
                            >
                                <div
                                    class="modal-dialog modal-dialog-centered modal-lg"
                                    role="document"
                                >
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Allocate - {{ typeObject.name }}
                                            </h5>
                                            <button
                                                type="button"
                                                class="close"
                                                @click="currentModal = null"
                                                aria-label="Close"
                                            >
                                                <span aria-hidden="true"
                                                    >&times;</span
                                                >
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <table class="table exclude-table">
                                                <tbody>
                                                    <tr>
                                                        <td
                                                            style="
                                                                padding-top: 0;
                                                                padding-bottom: 0;
                                                            "
                                                        >
                                                            <div
                                                                class="d-flex align-items-center"
                                                            >
                                                                <span
                                                                    class="mr-3 "
                                                                >
                                                                    {{
                                                                        "Allocate based on Revenue Streams"
                                                                    }}
                                                                </span>

                                                                <div
                                                                    class="kt-radio-inline"
                                                                    style="
                                                                        margin-bottom: 30px;
                                                                    "
                                                                >
                                                                    <label
                                                                        class="kt-radio kt-radio--success mb-0"
                                                                    >
                                                                        <input
                                                                            v-model="
                                                                                model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .is_as_revenue_percentages
                                                                            "
                                                                            @change="
                                                                                handleCheckboxChange(
                                                                                    typeObject.id,
                                                                                )
                                                                            "
                                                                            type="checkbox"
                                                                            class="allocate-checkbox"
                                                                        />
                                                                        <span></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr
                                                        v-for="(
                                                            product,
                                                            productIndex
                                                        ) in model[
                                                            typeObject.id
                                                        ].allocations.products"
                                                        :key="product.id"
                                                    >
                                                        <td>
                                                            <div
                                                                class="form-group"
                                                            >
                                                                <div
                                                                    class="row"
                                                                >
                                                                    <div
                                                                        class="col-9"
                                                                    >
                                                                        <label
                                                                            >Revenue
                                                                            Stream</label
                                                                        >
                                                                        <input
                                                                            :value="
                                                                                product.name
                                                                            "
                                                                            readonly
                                                                            class="form-control"
                                                                            type="text"
                                                                        />
                                                                        <input
                                                                            type="hidden"
                                                                            name="product_id"
                                                                            :value="
                                                                                product.id
                                                                            "
                                                                        />
                                                                    </div>
                                                                    <div
                                                                        class="col-3"
                                                                    >
                                                                        <label
                                                                            >Perc.%</label
                                                                        >
                                                                        <input
                                                                            v-model="
                                                                                model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .products[
                                                                                    productIndex
                                                                                ]
                                                                                    .percentage
                                                                            "
                                                                            type="text"
                                                                            class="form-control"
                                                                            :class="{
                                                                                'bg-light':
                                                                                    !model[
                                                                                        typeObject
                                                                                            .id
                                                                                    ]
                                                                                        .allocations
                                                                                        .is_as_revenue_percentages,
                                                                            }"
                                                                            :readonly="
                                                                                !model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .is_as_revenue_percentages
                                                                            "
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <div
                                                                class="form-group border-top pt-3"
                                                            >
                                                                <div
                                                                    class="row"
                                                                >
                                                                    <div
                                                                        class="col-9"
                                                                    >
                                                                        <label
                                                                            >Total</label
                                                                        >
                                                                        <input
                                                                            value="Total"
                                                                            readonly
                                                                            class="form-control"
                                                                            type="text"
                                                                        />
                                                                    </div>
                                                                    <div
                                                                        class="col-3"
                                                                    >
                                                                        <label
                                                                            >Total
                                                                            %</label
                                                                        >
                                                                        <input
                                                                            :value="
                                                                                calculateTotal(
                                                                                    typeObject.id,
                                                                                )
                                                                            "
                                                                            readonly
                                                                            class="form-control "
                                                                            :class="{
                                                                                'border-danger text-danger':
                                                                                    calculateTotal(
                                                                                        typeObject.id,
                                                                                    ) >
                                                                                    100,
                                                                                'border-success text-success':
                                                                                    calculateTotal(
                                                                                        typeObject.id,
                                                                                    ) <=
                                                                                    100,
                                                                            }"
                                                                        />
                                                                    </div>
                                                                </div>
                                                                <small
                                                                    v-if="
                                                                        calculateTotal(
                                                                            typeObject.id,
                                                                        ) > 100
                                                                    "
                                                                    class="text-danger d-block mt-2"
                                                                >
                                                                    ⚠️ Total
                                                                    percentage
                                                                    must not
                                                                    exceed 100%
                                                                </small>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="modal-footer">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                @click="currentModal = null"
                                            >
                                                Done
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                    </div>
                  </div>
                </div>
              </div>
              <!-- end cost per unit  -->

              <!-- start one time expense repeating  -->
              <div
                v-if="currentActiveTab == 'one_time_expense'"
                class="col-md-12">
                <div
                  v-for="(typeObject, typeIndex) in [
                    {
                      id: 'one_time_expense',
                      name: 'One Time Expense',
                    },
                  ]"
                  :key="typeIndex">
                  <div v-if="model[typeObject.id]">
                    <div
                      v-for="(item, index) in model[typeObject.id].sub_items"
                      :key="index"
                      class="row main-row-style">
                      <div class="max-w-trash col-md-1">
                        <div
                          v-if="index > 0"
                          class="d-flex flex-column justify-content-start align-items-start">
                          <label style="visibility: hidden"
                            >Delete
                            <br />
                            <span>a</span>
                          </label>
                          <button
                            @click="deleteRepeaterRow(index, typeObject.id)"
                            type="button"
                            class="btn btn-danger btn-md btn-danger-style ml-2"
                            title="Delete">
                            <i class="fas exclude-icon fa-trash trash-icon"></i>
                          </button>
                        </div>
                      </div>

                      <div class="col-md-2 col">
                        <Label :required="false"
                          >Expense <br />
                          Category</Label
                        >
                        <Select
                          filter
                          v-model="item.expense_category"
                          :options="expenseCategories"
                          @change="updateExpenseNamePerCategories(item)"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                      </div>
                      <div class="col-md-2 col">
                        <Label :required="false"
                          >Expense <br />
                          Name</Label
                        >
                        <Select
                          filter
                          v-model="item.expense_name_id"
                          :options="item.filteredExpenseNamesOptions"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                      </div>

                      <div class="col-md-1 col">
                        <Label :required="false"
                          >Date
                          <br />
                          <span style="visibility: hidden">a</span>
                        </Label>
                        <VueDatePicker
                          v-model="item.start_date"
                          month-picker
                          auto-apply
                          format="MMM-yyyy"
                          :min-date="new Date(studyStartDate)"
                          :start-date="new Date(studyStartDate)"
                          :max-date="
                            item.end_date ? new Date(item.end_date.year, item.end_date.month) : null
                          "></VueDatePicker>
                      </div>

                      <div class="col-md-1 col">
                        <Label :required="false"
                          >Amount <br />
                          <span style="visibility: hidden">a</span>
                        </Label>
                        <InputNumber
                          v-model="item.amount"
                          :min="0"
                          :minFractionDigits="0"
                          :maxFractionDigits="2"
                          suffix=" EGP"
                          fluid />
                      </div>
                      <div class="col-md-1 col">
                        <Label :required="false"
                          >Amortization <br />
                          Months</Label
                        >
                        <InputNumber
                          v-model="item.amortization_months"
                          :min="1"
                          :minFractionDigits="0"
                          :maxFractionDigits="0"
                          fluid />
                      </div>

                      <div class="col-md-1 col">
                        <Label :required="false"
                          >Payment <br />
                          Terms</Label
                        >
                        <Select
                          @change="showCustomPopup(item)"
                          filter
                          v-model="item.payment_terms"
                          :options="paymentTerms"
                          optionValue="id"
                          optionLabel="title"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                        <div
                          v-if="currentActiveCollectionModal == item"
                          @click.self="closePaymentModel()"
                          class="modal collection-modal fade show"
                          style="padding-right: 15px; display: block"
                          aria-modal="true">
                          <div
                            class="modal-dialog modal-sm modal-dialog-centered"
                            role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">Custom Payment</h5>
                                <button
                                  type="button"
                                  class="close"
                                  @click="closePaymentModel()">
                                  <span aria-hidden="true">×</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <div class="customize-elements">
                                  <table class="table exclude-table">
                                    <thead>
                                      <tr>
                                        <th class="text-center text-nowrap">Payment Rate %</th>
                                        <th class="text-center">Due In Days</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr
                                        v-for="rowIndex in [0, 1, 2, 3, 4]"
                                        :key="rowIndex">
                                        <td>
                                          <div>
                                            <InputNumber
                                              @input="handleRateChange(item, rowIndex, $event)"
                                              :placeholder="'Rate' + (rowIndex + 1)"
                                              :minFractionDigits="2"
                                              :maxFractionDigits="2"
                                              :step="0.25"
                                              :min="0"
                                              :max="100"
                                              mode="decimal"
                                              showButtons
                                              v-model="item.payment_rate[rowIndex]"
                                              suffix=" %"
                                              fluid />
                                          </div>
                                        </td>
                                        <td>
                                          <div class="">
                                            <Select
                                              filter
                                              v-model="item.due_days[rowIndex]"
                                              :options="collectionDueDays"
                                              optionValue="id"
                                              optionLabel="title"
                                              placeholder="Due Day"
                                              checkmark
                                              :highlightOnSelect="false"
                                              class="w-full md:w-56" />
                                          </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="text-center">
                                          Total:
                                          {{ calculatePaymentRatesTotal(item) }}%
                                        </td>
                                        <td></td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button
                                  type="button"
                                  class="btn btn-primary"
                                  @click="closePaymentModel()">
                                  Save
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-1 col">
                        <Label :required="false"
                          >Vat <br />
                          Rate</Label
                        >
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.vat_rate"
                            suffix=" %"
                            fluid />
                        </div>
                      </div>
                      <div class="col-md-1 col">
                        <Label :required="false"
                          >Withhold <br />
                          Rate</Label
                        >
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.withhold_tax_rate"
                            suffix=" %"
                            fluid />
                        </div>
                      </div>
                    </div>
                    <div class="container mt-4">
                      <div class="row">
                        <div
                          class="col-md-6"
                          style="width: 94%">
                          <input
                            @click="addNewItem(typeObject.id)"
                            data-repeater-create=""
                            type="button"
                            class="btn btn-primary btn-sm text-white"
                            value="Add Expense" />
                        </div>
                        <!-- <div class="col-md-2 ml-auto">
                                    <button
                                        @click="
                                            openAllocationModal(typeObject.id)
                                        "
                                        class="btn btn-success"
                                        type="button"
                                    >
                                        {{ "Allocate On Revenue Streams" }}
                                    </button>
                                </div> -->
                      </div>
                      <!-- <div
                                v-if="currentModal === typeObject.id"
                                class="modal fade show d-block"
                                tabindex="-1"
                                role="dialog"
                                @click.self="closeModal"
                                style="background-color: rgba(0, 0, 0, 0.5)"
                            >
                                <div
                                    class="modal-dialog modal-dialog-centered modal-lg"
                                    role="document"
                                >
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Allocate - {{ typeObject.name }}
                                            </h5>
                                            <button
                                                type="button"
                                                class="close"
                                                @click="currentModal = null"
                                                aria-label="Close"
                                            >
                                                <span aria-hidden="true"
                                                    >&times;</span
                                                >
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <table class="table exclude-table">
                                                <tbody>
                                                    <tr>
                                                        <td
                                                            style="
                                                                padding-top: 0;
                                                                padding-bottom: 0;
                                                            "
                                                        >
                                                            <div
                                                                class="d-flex align-items-center"
                                                            >
                                                                <span
                                                                    class="mr-3 "
                                                                >
                                                                    {{
                                                                        "Allocate based on Revenue Streams"
                                                                    }}
                                                                </span>

                                                                <div
                                                                    class="kt-radio-inline"
                                                                    style="
                                                                        margin-bottom: 30px;
                                                                    "
                                                                >
                                                                    <label
                                                                        class="kt-radio kt-radio--success mb-0"
                                                                    >
                                                                        <input
                                                                            v-model="
                                                                                model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .is_as_revenue_percentages
                                                                            "
                                                                            @change="
                                                                                handleCheckboxChange(
                                                                                    typeObject.id,
                                                                                )
                                                                            "
                                                                            type="checkbox"
                                                                            class="allocate-checkbox"
                                                                        />
                                                                        <span></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr
                                                        v-for="(
                                                            product,
                                                            productIndex
                                                        ) in model[
                                                            typeObject.id
                                                        ].allocations.products"
                                                        :key="product.id"
                                                    >
                                                        <td>
                                                            <div
                                                                class="form-group"
                                                            >
                                                                <div
                                                                    class="row"
                                                                >
                                                                    <div
                                                                        class="col-9"
                                                                    >
                                                                        <label
                                                                            >Revenue
                                                                            Stream</label
                                                                        >
                                                                        <input
                                                                            :value="
                                                                                product.name
                                                                            "
                                                                            readonly
                                                                            class="form-control"
                                                                            type="text"
                                                                        />
                                                                        <input
                                                                            type="hidden"
                                                                            name="product_id"
                                                                            :value="
                                                                                product.id
                                                                            "
                                                                        />
                                                                    </div>
                                                                    <div
                                                                        class="col-3"
                                                                    >
                                                                        <label
                                                                            >Perc.%</label
                                                                        >
                                                                        <input
                                                                            v-model="
                                                                                model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .products[
                                                                                    productIndex
                                                                                ]
                                                                                    .percentage
                                                                            "
                                                                            type="text"
                                                                            class="form-control"
                                                                            :class="{
                                                                                'bg-light':
                                                                                    !model[
                                                                                        typeObject
                                                                                            .id
                                                                                    ]
                                                                                        .allocations
                                                                                        .is_as_revenue_percentages,
                                                                            }"
                                                                            :readonly="
                                                                                !model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .is_as_revenue_percentages
                                                                            "
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <div
                                                                class="form-group border-top pt-3"
                                                            >
                                                                <div
                                                                    class="row"
                                                                >
                                                                    <div
                                                                        class="col-9"
                                                                    >
                                                                        <label
                                                                            >Total</label
                                                                        >
                                                                        <input
                                                                            value="Total"
                                                                            readonly
                                                                            class="form-control"
                                                                            type="text"
                                                                        />
                                                                    </div>
                                                                    <div
                                                                        class="col-3"
                                                                    >
                                                                        <label
                                                                            >Total
                                                                            %</label
                                                                        >
                                                                        <input
                                                                            :value="
                                                                                calculateTotal(
                                                                                    typeObject.id,
                                                                                )
                                                                            "
                                                                            readonly
                                                                            class="form-control "
                                                                            :class="{
                                                                                'border-danger text-danger':
                                                                                    calculateTotal(
                                                                                        typeObject.id,
                                                                                    ) >
                                                                                    100,
                                                                                'border-success text-success':
                                                                                    calculateTotal(
                                                                                        typeObject.id,
                                                                                    ) <=
                                                                                    100,
                                                                            }"
                                                                        />
                                                                    </div>
                                                                </div>
                                                                <small
                                                                    v-if="
                                                                        calculateTotal(
                                                                            typeObject.id,
                                                                        ) > 100
                                                                    "
                                                                    class="text-danger d-block mt-2"
                                                                >
                                                                    ⚠️ Total
                                                                    percentage
                                                                    must not
                                                                    exceed 100%
                                                                </small>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="modal-footer">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                @click="currentModal = null"
                                            >
                                                Done
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                    </div>
                  </div>
                </div>
              </div>
              <!-- end one time expense repeating  -->

              <!-- start expense per employees  -->
              <div
                v-if="currentActiveTab == 'expense_per_employee'"
                class="col-md-12">
                <div
                  v-for="(typeObject, typeIndex) in [
                    {
                      id: 'expense_per_employee',
                      name: 'Expense Per Employee',
                    },
                  ]"
                  :key="typeIndex">
                  <div v-if="model[typeObject.id]">
                    <div
                      v-for="(item, index) in model[typeObject.id].sub_items"
                      :key="index"
                      class="row flex-nowrap main-row-style">
                      <div class="col-md-1 max-w-trash">
                        <Label style="visibility: hidden">ddd</Label>
                        <div
                          :style="{ visibility: index == 0 ? 'hidden' : 'visible' }"
                          class="d-flex flex-column justify-content-start align-items-start">
                          <label style="visibility: hidden">Delete</label>
                          <button
                            @click="deleteRepeaterRow(index, typeObject.id)"
                            type="button"
                            class="btn btn-danger btn-md btn-danger-style ml-2"
                            title="Delete">
                            <i class="fas exclude-icon fa-trash trash-icon"></i>
                          </button>
                        </div>
                      </div>

                      <div class="col-md-2 col">
                        <Label :required="false"
                          >Expense <br />
                          Category</Label
                        >
                        <Select
                          filter
                          v-model="item.expense_category"
                          :options="expenseCategories"
                          @change="updateExpenseNamePerCategories(item)"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                      </div>
                      <div class="col col-md-2">
                        <Label :required="false"
                          >Expense <br />
                          Name</Label
                        >
                        <Select
                          filter
                          v-model="item.expense_name_id"
                          :options="item.filteredExpenseNamesOptions"
                          optionLabel="title"
                          optionValue="id"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                      </div>

                      <div class="col col-md-2">
                        <label>
                          Departments <br />
                          <span style="visibility: hidden">d</span>
                        </label>

                        <MultiSelect
                          v-model="item.department_ids"
                          showClear
                          :options="departments"
                          @change="updatePositionsBasedOnDepartments(item)"
                          optionValue="id"
                          optionLabel="title"
                          filter
                          placeholder=""
                          :maxSelectedLabels="50"
                          class="w-full md:w-80" />
                      </div>

                      <div class="col col-md-2">
                        <label>
                          Employee <br />
                          Position
                        </label>

                        <MultiSelect
                          v-model="item.position_ids"
                          showClear
                          :options="item.filteredPositionsOptions"
                          optionValue="id"
                          optionLabel="title"
                          filter
                          placeholder=""
                          :maxSelectedLabels="50"
                          class="w-full md:w-80" />
                      </div>

                      <div class="col col-md-1 min-w-140">
                        <Label :required="false"
                          >Start <br />
                          Date</Label
                        >
                        <VueDatePicker
                          v-model="item.start_date"
                          month-picker
                          auto-apply
                          format="MMM-yyyy"
                          :min-date="new Date(studyStartDate)"
                          :start-date="new Date(studyStartDate)"
                          :max-date="
                            item.end_date ? new Date(item.end_date.year, item.end_date.month) : null
                          "></VueDatePicker>
                      </div>

                      <div class="col-md-1 col">
                        <Label :required="false"
                          >Monthly Cost <br />
                          Per Unit
                        </Label>
                        <InputNumber
                          v-model="item.monthly_cost_of_unit"
                          :min="0"
                          :minFractionDigits="0"
                          :maxFractionDigits="2"
                          suffix=" EGP"
                          fluid />
                      </div>

                      <div class="col col-md-1 min-w-140">
                        <Label :required="false"
                          >End <br />
                          Date</Label
                        >
                        <VueDatePicker
                          v-model="item.end_date"
                          month-picker
                          auto-apply
                          format="MMM-yyyy"
                          :start-date="new Date(studyStartDate)"
                          :min-date="
                            item.start_date
                              ? new Date(item.start_date.year, item.start_date.month)
                              : null
                          "></VueDatePicker>
                      </div>
                      <div class="col col-md-1 min-w-160">
                        <Label :required="false"
                          >Payment <br />
                          Terms</Label
                        >
                        <Select
                          @change="showCustomPopup(item)"
                          filter
                          v-model="item.payment_terms"
                          :options="paymentTerms"
                          optionValue="id"
                          optionLabel="title"
                          placeholder=""
                          checkmark
                          :highlightOnSelect="false"
                          class="w-full md:w-56" />
                        <div
                          v-if="currentActiveCollectionModal == item"
                          @click.self="closePaymentModel()"
                          class="modal collection-modal fade show"
                          style="padding-right: 15px; display: block"
                          aria-modal="true">
                          <div
                            class="modal-dialog modal-sm modal-dialog-centered"
                            role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">
                                  Custom <br />
                                  Payment
                                </h5>
                                <button
                                  type="button"
                                  class="close"
                                  @click="closePaymentModel()">
                                  <span aria-hidden="true">×</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <div class="customize-elements">
                                  <table class="table exclude-table">
                                    <thead>
                                      <tr>
                                        <th class="text-center text-nowrap">Payment Rate %</th>
                                        <th class="text-center">Due In Days</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr
                                        v-for="rowIndex in [0, 1, 2, 3, 4]"
                                        :key="rowIndex">
                                        <td>
                                          <div>
                                            <InputNumber
                                              @input="handleRateChange(item, rowIndex, $event)"
                                              :placeholder="'Rate' + (rowIndex + 1)"
                                              :minFractionDigits="2"
                                              :maxFractionDigits="2"
                                              :step="0.25"
                                              :min="0"
                                              :max="100"
                                              mode="decimal"
                                              showButtons
                                              v-model="item.payment_rate[rowIndex]"
                                              suffix=" %"
                                              fluid />
                                          </div>
                                        </td>
                                        <td>
                                          <div class="">
                                            <Select
                                              filter
                                              v-model="item.due_days[rowIndex]"
                                              :options="collectionDueDays"
                                              optionValue="id"
                                              optionLabel="title"
                                              placeholder="Due Day"
                                              checkmark
                                              :highlightOnSelect="false"
                                              class="w-full md:w-56" />
                                          </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="text-center">
                                          Total:
                                          {{ calculatePaymentRatesTotal(item) }}%
                                        </td>
                                        <td></td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button
                                  type="button"
                                  class="btn btn-primary"
                                  @click="closePaymentModel()">
                                  Save
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col col-md-1 min-w-percentage">
                        <Label :required="false"
                          >Vat <br />
                          Rate</Label
                        >
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.vat_rate"
                            suffix=" %"
                            fluid />
                        </div>
                      </div>
                      <div class="col col-md-1 min-w-percentage">
                        <Label :required="false"
                          >Withhold <br />
                          Rate</Label
                        >
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :step="0.25"
                            :min="0"
                            :max="100"
                            mode="decimal"
                            showButtons
                            v-model="item.withhold_tax_rate"
                            suffix=" %"
                            fluid />
                        </div>
                      </div>

                      <IncreaseRateModal
                        :typeObject="typeObject"
                        :item="item"
                        :index="index"
                        :increase-years-formatted="increaseYearsFormatted"
                        :modals="modals">
                        <div
                          class="col-md-1 col"
                          v-if="increaseYearsFormatted.length">
                          <Label :required="false"
                            >Increase <br />
                            Rate</Label
                          >
                          <button
                            @click="modals.increaseRate.currentActive = typeObject.id + '-' + index"
                            class="btn btn-primary btn-md text-nowrap"
                            type="button">
                            Increase Rate
                          </button>
                        </div>
                      </IncreaseRateModal>
                    </div>
                    <div class="container mt-4">
                      <div class="row">
                        <div
                          class="col-md-6"
                          style="width: 94%">
                          <input
                            @click="addNewItem(typeObject.id)"
                            data-repeater-create=""
                            type="button"
                            class="btn btn-primary btn-sm text-white"
                            value="Add Expense" />
                        </div>
                        <!-- <div class="col-md-2 ml-auto">
                                    <button
                                        @click="
                                            openAllocationModal(typeObject.id)
                                        "
                                        class="btn btn-success"
                                        type="button"
                                    >
                                        {{ "Allocate On Revenue Streams" }}
                                    </button>
                                </div> -->
                      </div>
                      <!-- <div
                                v-if="currentModal === typeObject.id"
                                class="modal fade show d-block"
                                tabindex="-1"
                                role="dialog"
                                @click.self="closeModal"
                                style="background-color: rgba(0, 0, 0, 0.5)"
                            >
                                <div
                                    class="modal-dialog modal-dialog-centered modal-lg"
                                    role="document"
                                >
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Allocate - {{ typeObject.name }}
                                            </h5>
                                            <button
                                                type="button"
                                                class="close"
                                                @click="currentModal = null"
                                                aria-label="Close"
                                            >
                                                <span aria-hidden="true"
                                                    >&times;</span
                                                >
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <table class="table exclude-table">
                                                <tbody>
                                                    <tr>
                                                        <td
                                                            style="
                                                                padding-top: 0;
                                                                padding-bottom: 0;
                                                            "
                                                        >
                                                            <div
                                                                class="d-flex align-items-center"
                                                            >
                                                                <span
                                                                    class="mr-3 "
                                                                >
                                                                    {{
                                                                        "Allocate based on Revenue Streams"
                                                                    }}
                                                                </span>

                                                                <div
                                                                    class="kt-radio-inline"
                                                                    style="
                                                                        margin-bottom: 30px;
                                                                    "
                                                                >
                                                                    <label
                                                                        class="kt-radio kt-radio--success mb-0"
                                                                    >
                                                                        <input
                                                                            v-model="
                                                                                model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .is_as_revenue_percentages
                                                                            "
                                                                            @change="
                                                                                handleCheckboxChange(
                                                                                    typeObject.id,
                                                                                )
                                                                            "
                                                                            type="checkbox"
                                                                            class="allocate-checkbox"
                                                                        />
                                                                        <span></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr
                                                        v-for="(
                                                            product,
                                                            productIndex
                                                        ) in model[
                                                            typeObject.id
                                                        ].allocations.products"
                                                        :key="product.id"
                                                    >
                                                        <td>
                                                            <div
                                                                class="form-group"
                                                            >
                                                                <div
                                                                    class="row"
                                                                >
                                                                    <div
                                                                        class="col-9"
                                                                    >
                                                                        <label
                                                                            >Revenue
                                                                            Stream</label
                                                                        >
                                                                        <input
                                                                            :value="
                                                                                product.name
                                                                            "
                                                                            readonly
                                                                            class="form-control"
                                                                            type="text"
                                                                        />
                                                                        <input
                                                                            type="hidden"
                                                                            name="product_id"
                                                                            :value="
                                                                                product.id
                                                                            "
                                                                        />
                                                                    </div>
                                                                    <div
                                                                        class="col-3"
                                                                    >
                                                                        <label
                                                                            >Perc.%</label
                                                                        >
                                                                        <input
                                                                            v-model="
                                                                                model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .products[
                                                                                    productIndex
                                                                                ]
                                                                                    .percentage
                                                                            "
                                                                            type="text"
                                                                            class="form-control"
                                                                            :class="{
                                                                                'bg-light':
                                                                                    !model[
                                                                                        typeObject
                                                                                            .id
                                                                                    ]
                                                                                        .allocations
                                                                                        .is_as_revenue_percentages,
                                                                            }"
                                                                            :readonly="
                                                                                !model[
                                                                                    typeObject
                                                                                        .id
                                                                                ]
                                                                                    .allocations
                                                                                    .is_as_revenue_percentages
                                                                            "
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <div
                                                                class="form-group border-top pt-3"
                                                            >
                                                                <div
                                                                    class="row"
                                                                >
                                                                    <div
                                                                        class="col-9"
                                                                    >
                                                                        <label
                                                                            >Total</label
                                                                        >
                                                                        <input
                                                                            value="Total"
                                                                            readonly
                                                                            class="form-control"
                                                                            type="text"
                                                                        />
                                                                    </div>
                                                                    <div
                                                                        class="col-3"
                                                                    >
                                                                        <label
                                                                            >Total
                                                                            %</label
                                                                        >
                                                                        <input
                                                                            :value="
                                                                                calculateTotal(
                                                                                    typeObject.id,
                                                                                )
                                                                            "
                                                                            readonly
                                                                            class="form-control "
                                                                            :class="{
                                                                                'border-danger text-danger':
                                                                                    calculateTotal(
                                                                                        typeObject.id,
                                                                                    ) >
                                                                                    100,
                                                                                'border-success text-success':
                                                                                    calculateTotal(
                                                                                        typeObject.id,
                                                                                    ) <=
                                                                                    100,
                                                                            }"
                                                                        />
                                                                    </div>
                                                                </div>
                                                                <small
                                                                    v-if="
                                                                        calculateTotal(
                                                                            typeObject.id,
                                                                        ) > 100
                                                                    "
                                                                    class="text-danger d-block mt-2"
                                                                >
                                                                    ⚠️ Total
                                                                    percentage
                                                                    must not
                                                                    exceed 100%
                                                                </small>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="modal-footer">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                @click="currentModal = null"
                                            >
                                                Done
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                    </div>
                  </div>
                </div>
              </div>
              <!-- end cost per unit  -->
            </div>
            <!-- end tabs -->
          </div>
        </div>
      </div>
    </div>

    <!-- end one time expense -->
    <div class="row btn-for-submit--js">
      <div class="col-lg-6"></div>
      <div class="col-lg-6 kt-align-right">
        <button
          v-if="!isLoading"
          @click="submitForm"
          :disabled="disableSubmitBtn"
          data-button-value="save-and-go-to-next-value"
          type="submit"
          class="btn text-white active-style save-form">
          <!--  -->
          <span
            v-if="disableSubmitBtn"
            class="spinner-border mr-2 spinner-border-sm mb-1"
            role="status"
            aria-hidden="true"></span>
          <span
            class="text-lg"
            data-button-value="save-and-go-to-next-value"
            v-html="disableSubmitBtn ? 'Saving...' : 'Save & Go To Next'">
          </span>
        </button>
      </div>
    </div>
    <!-- <button
            
            type="submit"
            :class="[
                'btn btn-rev float-right',
                { disableSubmitBtn: 'disabled' },
            ]"
            name="submit_button"
            value="next"
        >
            Next
        </button>
        <button
            v-if="!isLoading"
            @click="submitForm"
            type="submit"
            :class="[
                'btn btn-rev float-right main-page-button',
                { disableSubmitBtn: 'disabled' },
            ]"
            name="submit_button"
            value="save"
        >
            Save & Go To Main Page
        </button> -->
  </div>
</template>
<script setup>
import InputNumber from 'primevue/inputnumber'
import MultiSelect from 'primevue/multiselect'
import Select from 'primevue/select'
import Loading from '../../Common/Loading.vue'
import IncreaseRateModal from './modals/IncreaseRateModal.vue'
// import VueLoadingTemplate from 'vue-loading-template';
import axios from 'axios'
import { onMounted, ref } from 'vue'
import Helper from '../../../Helpers/Helper'
import Label from '../../Form/Label.vue'
// import TextInput from "../Form/TextInput.vue";
const isLoading = ref(true)
// modals.increaseRate.currentActive = null
const modals = ref({
  increaseRate: {
    currentActive: null,
  },
})
let expenseNamesPerCategories = []
const percentageOf = Helper.getPercentageOf()
const currentActiveTab = ref('fixed_monthly_repeating_amount')
const expenseTypes = ref(Helper.getExpenseTypes())
const updateExpenseNamePerCategories = (item) => {
  item.expense_name_id = null
  item.filteredExpenseNamesOptions = expenseNamesPerCategories[item.expense_category] || []
}
const updateRevenueCategoriesBasedOnRevenue = (item) => {
  item.stream_category_ids = []
  item.filteredRevenueCategoriesOptions = []
  item.revenue_stream_type.forEach((revenueStream) => {
    item.filteredRevenueCategoriesOptions.push(...revenueCategoriesPerRevenue[revenueStream])
  })
}
const updatePositionsBasedOnDepartments = (item) => {
  item.position_ids = []
  item.filteredPositionsOptions = []

  item.department_ids.forEach((departmentId) => {
    console.log(positionsPerDepartments, departmentId)
    item.filteredPositionsOptions.push(...positionsPerDepartments[departmentId])
  })
}
// const getRevenueCategoryBasedOnRevenues = (item, selectedRevenues) => {
//   let results = []
//   console.log('ee')
//   selectedRevenues.forEach((selectedRevenue) => {
//     results.push(...revenueCategoriesPerRevenue.value[selectedRevenue])
//   })
//   return results
// }

// const formattedNumbers = ref({
//     one_time_expense: {},
//     fixed_monthly_repeating_amount: {},
//     cost_per_unit: {},
//     expense_as_percentage: {},
// });
// const initializeFormattedNumbers = () => {
//     const types = [
//         "expense_as_percentage",
//         "cost_per_unit",
//         "fixed_monthly_repeating_amount",
//         "one_time_expense",
//     ];

//     types.forEach((type) => {
//         console.log(model.value[type]);
//         if (model.value[type]?.sub_items) {
//             model.value[type].sub_items.forEach((item, index) => {
//                 // للأنواع التي لها amount
//                 if (
//                     [
//                         "fixed_monthly_repeating_amount",
//                         "one_time_expense",
//                     ].includes(type)
//                 ) {
//                     formattedNumbers.value[type][index] = item.amount
//                         ? Helper.number_format(item.amount)
//                         : "";
//                 }
//                 // للأنواع التي لها monthly_cost_of_unit
//                 if (type === "cost_per_unit") {
//                     formattedNumbers.value[type][index] =
//                         item.monthly_cost_of_unit
//                             ? Helper.number_format(item.monthly_cost_of_unit)
//                             : "";
//                 }
//             });
//         }
//     });
// };
// const formatNumber = (value) => {
//     return Helper.number_format(value || 0);
// };
// const storeValueUnFormatted = (item, index, formattedValue) => {
//     // تخزين القيمة غير المنسقة في الموديل
//     item.amount = Helper.number_unformat(formattedValue);

//     // تخزين القيمة المنسقة للعرض
//     formattedNumbers.value[index] = Helper.number_format(item.amount);
// };

// const storeValueUnFormatted = (item,index) => {
// 	item.amount = Helper.number_unformat(item.amount);

// 	formattedNumbers.value[index] = Helper.number_format(item.amount);
// }

// const getFormattedValue = (type, index, field = "amount") => {
//     return formattedNumbers.value[type]?.[index] || "";
// };

// دالة للتحديث
const updateFormattedValue = (item, value, field = 'amount', numberOfDecimals = 0) => {
  const rawValue = value
  item[field] = Helper.number_unformat(rawValue)
  item[field + '_formatted'] = Helper.number_format(item[field], numberOfDecimals)
}

const showCustomPopup = (item) => {
  if (item.payment_terms == 'customize') {
    currentActiveCollectionModal.value = item
  }
}
const calculatePaymentRatesTotal = (item) => {
  if (!item.payment_rate) {
    return 0
  }
  const total = item.payment_rate.reduce((sum, rate) => {
    const numericValue = parseFloat(rate) || 0
    return sum + numericValue
  }, 0)

  return total.toFixed(2)
}
const handleRateChange = (item, rowIndex, event) => {
  item.payment_rate[rowIndex] = Helper.number_unformat(
    event.target ? event.target.value : event.value, // in case of InputPercentage Field
  )

  const total = calculatePaymentRatesTotal(item)
  if (total > 100) {
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'Total Rates Exceed 100%',
    })
  }
}

const closePaymentModel = () => {
  currentActiveCollectionModal.value = null
}
const currentActiveCollectionModal = ref(null)
const collectionDueDays = Helper.getCollectionDays()
const currentModal = ref(null)
const paymentTerms = Helper.getPaymentTerms()
const disableSubmitBtn = ref(false)
const model = ref({})
const revenueStreams = ref([])
const departments = ref([])
const expenseCategories = ref([])
const increaseYearsFormatted = ref([])
let revenueCategoriesPerRevenue = []
let positionsPerDepartments = []

const studyStartDate = ref(null)
const submitUrl = ref(null)

const getModelData = () => {
  const body = document.querySelector('body')

  const csrfToken = body.dataset.token
  const baseUrl = body.dataset.baseUrl
  const companyId = body.dataset.currentCompanyId
  const studyId = body.dataset.studyId
  const lang = body.dataset.lang
  const fetchOldDataUrl = `${baseUrl}/${lang}/${companyId}/non-banking-financial-services/study/${studyId}/expenses-fetch-old-data`
  axios
    .get(fetchOldDataUrl, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
    })
    .then((response) => {
      //  revenueStreamsPerBusinessUnits.value = response.data.revenueStreamsPerBusinessUnits
      studyStartDate.value = response.data.studyStartDate
      model.value = response.data.model
      //   initializeFormattedNumbers();
      expenseCategories.value = response.data.expenseCategories
      increaseYearsFormatted.value = response.data.increaseYearsFormatted
      revenueStreams.value = response.data.revenueStreams
      departments.value = response.data.departments
      // businessUnits.value = response.data.businessUnitsForMultiSelect
      expenseNamesPerCategories = response.data.expenseNamesPerCategories
      revenueCategoriesPerRevenue = response.data.revenueCategoriesPerRevenue
      positionsPerDepartments = response.data.positionsPerDepartments
      submitUrl.value = response.data.submitUrl
      isLoading.value = false
    })
    .catch((error) => {
      isLoading.value = false
      const errorMessage = error.response?.data?.message || 'An error occurred'
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: errorMessage,
      })
    })
}

onMounted(() => {
  getModelData()
})

// const activeModal = ref(null)
// const updateSalesRevenuesBasedOnBusinessUnits = (item) => {}
// const updateSalesRevenuesBasedOnBusinessUnits = (item) => {
//     const businessUnits = item.businessUnits;

//     const body = document.querySelector("body");

//     //  const csrfToken = body.dataset.token;
//     const baseUrl = body.dataset.baseUrl;
//     const projectId = body.dataset.projectId;
//     const lang = body.dataset.lang;
//     axios
//         .post(
//             `${baseUrl}/${lang}/${projectId}/get-sales-revenues-based-on-business-units`,
//             { businessUnits },
//         )
//         .then((res) => {
//             revenueStreamsPerBusinessUnits.value[item.id] =
//                 res.data.revenue_streams;
//             item.revenueStreams = [];
//         })
//         .catch((err) => {
//             console.error("error", err);
//         });
// };

const calculateTotal = (itemType) => {
  const data = model.value[itemType].allocations
  if (!data || !data.is_as_revenue_percentages) return 0
  return data.products.reduce(
    (sum, p) => sum + (parseFloat(Helper.number_unformat(p.percentage)) || 0),
    0,
  )
}

const handleCheckboxChange = (itemType) => {
  const data = model.value[itemType].allocations
  const productCount = data.products.length
  const equalPercentage = productCount > 0 ? 100 / productCount : 0
  data.products.forEach((p) => {
    if (data.is_as_revenue_percentages) {
      // When checkbox is checked, distribute percentages equally
      p.percentage = equalPercentage.toFixed(2)
    } else {
      // When checkbox is unchecked, set all percentages to zero
      p.percentage = 0
    }
  })
}

const openAllocationModal = (type) => {
  currentModal.value = type
}

const addNewItem = (type) => {
  const emptyRow = model.value[type].empty_row
  return model.value[type].sub_items.push({ ...emptyRow })
}
const deleteRepeaterRow = (index, type) => {
  model.value[type].sub_items.splice(index, 1)
}

// const openModal = (index) => {
//     activeModal.value = index;
//     document.body.classList.add("modal-open");
// };

const closeModal = () => {
  currentModal.value = null
  document.body.classList.remove('modal-open')
}
const submitForm = (e) => {
  model.value.submit_button = e.target.value
  disableSubmitBtn.value = true
  const body = document.querySelector('body')
  const csrfToken = body.dataset.token
  axios
    .post(submitUrl.value, model.value, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
    })
    .then((response) => {
      disableSubmitBtn.value = false
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Your data has been saved',
        draggable: true,
        timer: 2000,
      }).then((res) => {
        window.location.href = response.data.redirectTo
      })
    })
    .catch((error) => {
      const errorMessage = error.response?.data?.message || 'An error occurred'
      disableSubmitBtn.value = false
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: errorMessage,
      })
    })
}
</script>
<style scoped>
.max-w-70px {
  max-width: 70px !important;
}

.max-w-185px {
  max-width: 185px !important;
}

.max-w-200px {
  max-width: 200px !important;
}

.max-w-530px {
  max-width: 530px !important;
}

/* Fix z-index for PrimeVue Select dropdown inside modal */
:deep(.p-select-overlay) {
  z-index: 1060 !important;
}

/* Alternative: Fix for all PrimeVue overlays inside modals */
.modal {
  z-index: 990 !important;
}

.modal-body {
  max-height: 800px;
}

:deep(.p-skeleton) {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s ease-in-out infinite;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

.btn-div {
  padding: 0 !important;
  width: 30px !important;
  height: 30px !important;
}

.btn-div span {
  font-size: 20px !important;
  cursor: pointer;
}

.trash_icon {
  width: 30px;
  height: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
}

:deep(.p-component, .dp__input) {
  height: 38px !important;
}

:deep(.p-select) {
  border: 1px solid #cce2fd;
}

/* Or target the input more specifically */
:deep(.dp__input) {
  height: 38px !important;
}

:deep(.p-select-label) {
  display: flex;
  align-items: center;
}
:deep(.p-multiselect-label-container) {
  display: flex;
  align-items: center;
}

.btn-danger-style {
  padding-right: 9px;
  padding-left: 9px;
  padding-top: 3px;
  padding-bottom: 5px;
}

.btn-danger-style i {
  padding-right: 0 !important;
  color: white !important;
  font-size: 0.9rem !important;
}

.max-w-150 {
  width: 150px !important;
  min-width: 150px !important;
  max-width: 150px !important;
}

.max-w-175 {
  width: 175px !important;
  min-width: 175px !important;
  max-width: 175px !important;
}
.col {
  flex-shrink: 1;
  min-width: 0;
}
.min-w-160 {
  min-width: 160px !important;
}
.min-w-140 {
  min-width: 140px !important;
}
.min-w-percentage {
  min-width: 126px !important;
}
* {
  min-width: 0;
}
.max-w-trash {
  max-width: 55px !important;
}

:deep(.p-select-label.p-placeholder),
:deep(.p-select-label) {
  color: black !important;
}
:deep(.p-multiselect-label) {
  color: black !important;
}
:deep(.p-select:not(.p-disabled).p-focus) {
  border-color: #4d9afa;
}

:deep(.p-select) {
  border-color: #4d9afa !important;
}

:deep(.main-row-style:nth-child(even) .p-select),
:deep(.main-row-style:nth-child(even) .p-multiselect),
:deep(.main-row-style:nth-child(even) .dp__input),
:deep(.main-row-style:nth-child(even) .p-inputtext) {
  border: 1px solid #54aaa6 !important;
}
:deep(.main-row-style:nth-child(odd) .p-multiselect),
:deep(.main-row-style:nth-child(odd) .p-select),
:deep(.main-row-style:nth-child(odd) .dp__input),
:deep(.main-row-style:nth-child(odd) .p-inputtext) {
  border: 1px solid #4d9afa !important;
}
</style>
<style scoped></style>
