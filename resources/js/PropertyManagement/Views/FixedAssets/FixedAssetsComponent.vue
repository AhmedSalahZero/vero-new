<template>
  <div>
    <div
      class="row"
      v-if="currentActiveTab">
      <div class="col-md-12">
        <div class="kt-portlet">
          <div class="kt-portlet__body exclude">
            <!-- start tabs -->
            <div
              v-if="fixedAssetTypes.length"
              class="form-group row justify-content-center"
              style="border-bottom: 1px solid green; padding-bottom: 10px">
              <div class="d-flex align-items-center justify-content-start mr-auto">
                <button
                  v-for="(fixedAssetType, index) in fixedAssetTypes"
                  :key="index"
                  @click="currentActiveTab = fixedAssetType.id"
                  :class="[
                    'btn mb-5 type-btn btn btn-outline-info ',
                    {
                      active: currentActiveTab == fixedAssetType.id,
                    },
                  ]">
                  {{ fixedAssetType.title }}
                </button>
              </div>
            </div>

            <Loading :isLoading="isLoading"></Loading>

            <div v-if="!isLoading">
              <!-- start fixed monthly repeating  -->
              <div
                v-if="currentActiveTab == 'ffe' && fixedAssetTypes.length"
                class="col-md-12">
                <div
                  v-if="model.ffe"
                  class="overflow-scroll">
                  <div
                    v-for="(item, index) in model.ffe"
                    :key="index"
                    class="row main-row-style">
                    <div
                      class="col-md-1 max-w-trash d-flex justify-content-center align-items-center">
                      <div
                        v-if="Number(index) > 0"
                        class="d-flex flex-column justify-content-start align-items-start">
                        <label style="visibility: hidden">Delete</label>
                        <button
                          @click="deleteRepeaterRow(index, 'ffe')"
                          type="button"
                          class="btn btn-danger btn-md btn-danger-style ml-2"
                          title="Delete">
                          <i class="fas exclude-icon fa-trash trash-icon"></i>
                        </button>
                      </div>
                    </div>

                    <div class="col-md-2 col">
                      <Label :required="false"
                        >Item <br />
                        Name</Label
                      >
                      <Select
                        filter
                        v-model="item.name_id"
                        :options="selects.generalFixedAssetNames"
                        optionLabel="title"
                        optionValue="id"
                        placeholder=""
                        checkmark
                        :highlightOnSelect="false"
                        class="w-full md:w-56" />
                    </div>
                    <div class="col-md-1 col">
                      <Label :required="false"
                        >Item <br />
                        Cost</Label
                      >
                      <InputNumber
                        v-model="item.ffe_item_cost"
                        :min="0"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
                        suffix=" EGP"
                        fluid />
                    </div>

                    <div class="col-md-1 col">
                      <Label :required="false"
                        >Contingency <br />
                        Rate %</Label
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
                          v-model="item.contingency_rate"
                          suffix=" %"
                          fluid />
                      </div>
                    </div>

                    <div class="col-md-1 col">
                      <Label :required="false"
                        >Cost Annual <br />
                        Increase %</Label
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
                          v-model="item.cost_annual_increase_rate"
                          suffix=" %"
                          fluid />
                      </div>
                    </div>

                    <!-- <div class="col-md-2 col">
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

                      <div class="col-md-1 col min-w-140">
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

                      <div class="col-md-1 col min-w-140">
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
                      </div> -->
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

                    <div class="col-md-2 col">
                      <Label :required="false"
                        >Depreciation <br />
                        Duration</Label
                      >
                      <Select
                        filter
                        v-model="item.depreciation_duration"
                        :options="depreciationDurations"
                        optionLabel="title"
                        optionValue="id"
                        placeholder=""
                        checkmark
                        :highlightOnSelect="false"
                        class="w-full md:w-56" />
                    </div>

                    <div class="col-md-1 col">
                      <Label :required="false">Replacement <br />Cost %</Label>
                      <div class="form-group">
                        <InputNumber
                          :minFractionDigits="2"
                          :maxFractionDigits="2"
                          :step="0.25"
                          :min="0"
                          :max="100"
                          mode="decimal"
                          showButtons
                          v-model="item.replacement_cost_rate"
                          suffix=" %"
                          fluid />
                      </div>
                    </div>

                    <div class="col-md-2 col">
                      <Label :required="false"
                        >Replacement <br />
                        Interval</Label
                      >
                      <Select
                        filter
                        v-model="item.replacement_interval"
                        :options="replacementIntervals"
                        optionLabel="title"
                        optionValue="id"
                        placeholder=""
                        checkmark
                        :highlightOnSelect="false"
                        class="w-full md:w-56" />
                    </div>
                    <template
                      v-for="(dateFormatted, dateAsIndex) in dates"
                      :key="dateAsIndex">
                      <div
                        v-if="!hideTablesDates.ffe.includes(Number(dateAsIndex))"
                        class="col min-w-percentage">
                        <Label :required="false">{{ dateFormatted }} <br />Count #</Label>
                        <div class="form-group">
                          <InputNumber
                            :minFractionDigits="0"
                            :maxFractionDigits="0"
                            :step="1"
                            :min="0"
                            mode="decimal"
                            showButtons
                            v-model="item.ffe_counts[dateAsIndex]"
                            suffix=""
                            fluid />
                        </div>
                      </div>

                      <div
                        v-if="
                          lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
                          lastMonthIndexInEachYear.length > 1
                        "
                        class="col min-w-percentage">
                        <Label :required="false">
                          <div
                            class="d-flex justify-content-center align-items-center"
                            style="gap: 10px">
                            <span class="text-center d-inline-block"
                              >Total Yr. <br />
                              {{ getYearsFromDates[dateAsIndex] }}
                            </span>
                            <i
                              @click="hideOrExpandMyYear('ffe', Number(dateAsIndex))"
                              title="Expand / Collapse"
                              class="cursor-pointer fa fa-expand-arrows-alt text-primary exclude-icon"></i>
                          </div>
                        </Label>
                        <div class="form-group">
                          <td
                            v-if="
                              lastMonthIndexInEachYear.includes(Number(dateAsIndex)) &&
                              lastMonthIndexInEachYear.length > 1
                            ">
                            <InputNumber
                              :min="0"
                              v-model="ffeTotals.subRowTotals[index]['per_year'][dateAsIndex]"
                              input-class="text-center"
                              :minFractionDigits="0"
                              :maxFractionDigits="2"
                              suffix=""
                              disabled
                              fluid />
                            <i
                              style="visibility: hidden"
                              class="fa fa-ellipsis-h"></i>
                          </td>
                        </div>
                      </div>
                    </template>
                  </div>
                  <div class="container mt-4">
                    <div class="row">
                      <div
                        class="col-md-6"
                        style="width: 94%">
                        <input
                          @click="addNewItem('ffe')"
                          data-repeater-create=""
                          type="button"
                          class="btn btn-primary btn-sm text-white mb-4"
                          value="Add Fixed Asset" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- end fixed monthly repeating  -->

              <!-- start per employee  -->
              <div
                v-if="currentActiveTab == 'per-employee'"
                class="col-md-12">
                <div
                  v-if="model['per-employee']"
                  class="overflow-scroll">
                  <div
                    v-for="(item, index) in model['per-employee']"
                    :key="index"
                    class="row main-row-style">
                    <div
                      class="col-md-1 max-w-trash d-flex justify-content-center align-items-center">
                      <div
                        v-if="Number(index) > 0"
                        class="d-flex flex-column justify-content-start align-items-start">
                        <label style="visibility: hidden">Delete</label>
                        <button
                          @click="deleteRepeaterRow(index, 'per-employee')"
                          type="button"
                          class="btn btn-danger btn-md btn-danger-style ml-2"
                          title="Delete">
                          <i class="fas exclude-icon fa-trash trash-icon"></i>
                        </button>
                      </div>
                    </div>

                    <div class="col-md-2 col">
                      <Label :required="false"
                        >Item <br />
                        Name</Label
                      >
                      <Select
                        filter
                        v-model="item.name_id"
                        :options="selects.perEmployeeFixedAssetNames"
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

                    <div class="col-md-1 col">
                      <Label :required="false"
                        >Item <br />
                        Cost</Label
                      >
                      <InputNumber
                        v-model="item.ffe_item_cost"
                        :min="0"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
                        suffix=" EGP"
                        fluid />
                    </div>

                    <div class="col-md-1 col">
                      <Label :required="false"
                        >Contingency <br />
                        Rate %</Label
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
                          v-model="item.contingency_rate"
                          suffix=" %"
                          fluid />
                      </div>
                    </div>

                    <div class="col-md-1 col">
                      <Label :required="false"
                        >Cost Annual <br />
                        Increase %</Label
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
                          v-model="item.cost_annual_increase_rate"
                          suffix=" %"
                          fluid />
                      </div>
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

                    <div class="col-md-2 col">
                      <Label :required="false"
                        >Depreciation <br />
                        Duration</Label
                      >
                      <Select
                        filter
                        v-model="item.depreciation_duration"
                        :options="depreciationDurations"
                        optionLabel="title"
                        optionValue="id"
                        placeholder=""
                        checkmark
                        :highlightOnSelect="false"
                        class="w-full md:w-56" />
                    </div>

                    <div class="col-md-1 col">
                      <Label :required="false">Replacement <br />Cost %</Label>
                      <div class="form-group">
                        <InputNumber
                          :minFractionDigits="2"
                          :maxFractionDigits="2"
                          :step="0.25"
                          :min="0"
                          :max="100"
                          mode="decimal"
                          showButtons
                          v-model="item.replacement_cost_rate"
                          suffix=" %"
                          fluid />
                      </div>
                    </div>

                    <div class="col-md-2 col">
                      <Label :required="false"
                        >Replacement <br />
                        Interval</Label
                      >
                      <Select
                        filter
                        v-model="item.replacement_interval"
                        :options="replacementIntervals"
                        optionLabel="title"
                        optionValue="id"
                        placeholder=""
                        checkmark
                        :highlightOnSelect="false"
                        class="w-full md:w-56" />
                    </div>
                    <div class="col-md-1 col">
                      <Label :required="false"
                        >Count <br />
                        <span style="visibility: hidden">d</span>
                      </Label>

                      <div class="form-group">
                        <InputNumber
                          :minFractionDigits="0"
                          :maxFractionDigits="0"
                          :step="1"
                          :min="0"
                          mode="decimal"
                          showButtons
                          v-model="item.counts"
                          suffix=""
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
                          @click="addNewItem('per-employee')"
                          data-repeater-create=""
                          type="button"
                          class="btn btn-primary btn-sm text-white mb-4"
                          value="Add Per Employee" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- end per employee -->

              <!-- start end new brach  -->
              <div
                v-if="currentActiveTab == 'new-branch' && hasMicrofinance"
                class="col-md-12">
                <div
                  v-if="model.ffe"
                  class="overflow-scroll">
                  <div
                    v-for="(item, index) in model['new-branch']"
                    :key="index"
                    class="row main-row-style">
                    <div
                      class="col-md-1 max-w-trash d-flex justify-content-center align-items-center">
                      <div
                        v-if="Number(index) > 0"
                        class="d-flex flex-column justify-content-start align-items-start">
                        <label style="visibility: hidden">Delete</label>
                        <button
                          @click="deleteRepeaterRow(index, 'new-branch')"
                          type="button"
                          class="btn btn-danger btn-md btn-danger-style ml-2"
                          title="Delete">
                          <i class="fas exclude-icon fa-trash trash-icon"></i>
                        </button>
                      </div>
                    </div>

                    <div class="col-md-2 col">
                      <Label :required="false"
                        >Item <br />
                        Name</Label
                      >
                      <Select
                        filter
                        v-model="item.name_id"
                        :options="selects.newBranchFixedAssetNames"
                        optionLabel="title"
                        optionValue="id"
                        placeholder=""
                        checkmark
                        :highlightOnSelect="false"
                        class="w-full md:w-56" />
                    </div>
                    <div class="col-md-1 col">
                      <Label :required="false"
                        >Item <br />
                        Cost</Label
                      >
                      <InputNumber
                        v-model="item.ffe_item_cost"
                        :min="0"
                        :minFractionDigits="0"
                        :maxFractionDigits="0"
                        suffix=" EGP"
                        fluid />
                    </div>

                    <div class="col-md-1 col">
                      <Label :required="false"
                        >Contingency <br />
                        Rate %</Label
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
                          v-model="item.contingency_rate"
                          suffix=" %"
                          fluid />
                      </div>
                    </div>

                    <div class="col-md-1 col">
                      <Label :required="false"
                        >Cost Annual <br />
                        Increase %</Label
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
                          v-model="item.cost_annual_increase_rate"
                          suffix=" %"
                          fluid />
                      </div>
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

                    <div class="col-md-2 col">
                      <Label :required="false"
                        >Depreciation <br />
                        Duration</Label
                      >
                      <Select
                        filter
                        v-model="item.depreciation_duration"
                        :options="depreciationDurations"
                        optionLabel="title"
                        optionValue="id"
                        placeholder=""
                        checkmark
                        :highlightOnSelect="false"
                        class="w-full md:w-56" />
                    </div>

                    <div class="col-md-1 col">
                      <Label :required="false">Replacement <br />Cost %</Label>
                      <div class="form-group">
                        <InputNumber
                          :minFractionDigits="2"
                          :maxFractionDigits="2"
                          :step="0.25"
                          :min="0"
                          :max="100"
                          mode="decimal"
                          showButtons
                          v-model="item.replacement_cost_rate"
                          suffix=" %"
                          fluid />
                      </div>
                    </div>

                    <div class="col-md-2 col">
                      <Label :required="false"
                        >Replacement <br />
                        Interval</Label
                      >
                      <Select
                        filter
                        v-model="item.replacement_interval"
                        :options="replacementIntervals"
                        optionLabel="title"
                        optionValue="id"
                        placeholder=""
                        checkmark
                        :highlightOnSelect="false"
                        class="w-full md:w-56" />
                    </div>

                    <div class="col-md-1 col">
                      <Label :required="false"
                        >Count <br />
                        <span style="visibility: hidden">d</span>
                      </Label>

                      <div class="form-group">
                        <InputNumber
                          :minFractionDigits="0"
                          :maxFractionDigits="0"
                          :step="1"
                          :min="0"
                          mode="decimal"
                          showButtons
                          v-model="item.counts"
                          suffix=""
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
                          @click="addNewItem('new-branch')"
                          data-repeater-create=""
                          type="button"
                          class="btn btn-primary btn-sm text-white mb-4"
                          value="Add New Branch" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- end new brach  -->
            </div>
            <!-- end tabs -->
          </div>
        </div>

        <div
          class="kt-portlet"
          v-if="!isLoading && currentActiveTab == 'ffe'">
          <div class="kt-portlet__body">
            <div class="row mt-3">
              <div class="col-md-6">
                <div
                  class="d-flex align-items-center"
                  style="gap: 10px">
                  <div
                    class="d-flex align-items-start"
                    style="gap: 5px">
                    <RadioButton
                      v-model="
                        model.generalFixedAssetsFundingStructure.is_fully_funded_though_equity
                      "
                      :inputId="'ffe_1_element'"
                      :value="1" />
                    <label :for="'ffe_1_element'">Fully Funded Through Equity</label>
                  </div>
                  <div
                    class="d-flex align-items-start"
                    style="gap: 5px">
                    <RadioButton
                      v-model="
                        model.generalFixedAssetsFundingStructure.is_fully_funded_though_equity
                      "
                      :inputId="'ffe_2_element'"
                      :value="0" />
                    <label :for="'ffe_2_element'">Funded Through Equity & Debt</label>
                  </div>
                </div>
              </div>
            </div>

            <div
              class="row mt-5"
              v-if="
                !model.generalFixedAssetsFundingStructure.is_fully_funded_though_equity &&
                Object.keys(ffeFundingDates).length
              ">
              <div class="col-md-11">
                <div class="d-flex align-items-center">
                  <h3 class="font-weight-bold form-label kt-subheader__title small-caps">
                    Funding Structure
                  </h3>
                </div>
              </div>

              <div class="col-md-12">
                <hr style="background-color: lightgray" />
              </div>
            </div>
            <div
              v-if="
                !model.generalFixedAssetsFundingStructure.is_fully_funded_though_equity &&
                Object.keys(ffeFundingDates).length
              "
              v-show="true"
              class="row mt-4">
              <div class="col-md-12 overflow-scroll">
                <table class="table">
                  <thead>
                    <tr>
                      <th
                        class="form-label font-weight-bold text-center align-middle header-border-down first-column-th-class">
                        Item
                      </th>

                      <template
                        v-for="(dateFormatted, dateAsIndex) in ffeFundingDates"
                        :key="dateAsIndex">
                        <th
                          class="form-label expandable-th-amount-input font-weight-bold text-center align-middle header-border-down">
                          <span class="text-left d-inline-block">{{ dateFormatted }}</span>
                        </th>
                      </template>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- start net disbursements -->
                    <tr :data-repeater-style="true">
                      <td>
                        <div class="d-flex flex-column align-items-start">
                          <input
                            :value="'Direct FFE Amounts'"
                            :disabled="true"
                            class="form-control min-width-hover-300 text-left mt-3"
                            type="text" />

                          <i
                            style="visibility: hidden"
                            class="fa fa-ellipsis-h"></i>
                        </div>
                      </td>
                      <template
                        v-for="(dateFormatted, dateAsIndex) in ffeFundingDates"
                        :key="Number(dateAsIndex)">
                        <td>
                          <div class="d-flex flex-column align-items-center">
                            <InputNumber
                              v-model="ffeDirectAmounts[Number(dateAsIndex)]"
                              :min="0"
                              disabled
                              input-class="text-center"
                              :minFractionDigits="0"
                              :maxFractionDigits="0"
                              suffix=" EGP"
                              fluid />
                          </div>
                        </td>
                      </template>
                    </tr>
                    <!-- end net disbursements -->
                    <!-- start equity funding rate -->
                    <tr :data-repeater-style="true">
                      <td>
                        <div class="d-flex flex-column align-items-start">
                          <input
                            :value="'Equity Funding Rate (%)'"
                            :disabled="true"
                            class="form-control min-width-hover-300 text-left mt-2"
                            type="text" />
                          <i
                            style="visibility: hidden"
                            class="fa fa-ellipsis-h"></i>
                        </div>
                      </td>
                      <template
                        v-for="(dateFormatted, dateAsIndex) in ffeFundingDates"
                        :key="Number(dateAsIndex)">
                        <td>
                          <!-- {{ logRender(leasingRevenueStreamBreakdownItem.id, dateAsIndex) }} -->
                          <div
                            class="d-flex min-w-percentage mx-auto mt-2 text-center flex-column align-items-center">
                            <InputNumber
                              v-model="
                                model.generalFixedAssetsFundingStructure.equity_funding_rates[
                                  Number(dateAsIndex)
                                ]
                              "
                              :min="0"
                              :max="100"
                              input-class="text-center"
                              :minFractionDigits="2"
                              suffix=" %"
                              fluid />

                            <i
                              @click="
                                handleRepeatRight(
                                  model.generalFixedAssetsFundingStructure.equity_funding_rates,
                                  Number(dateAsIndex),
                                  ffeFundingDates,
                                )
                              "
                              class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                              title="Repeat Right"></i>
                          </div>
                        </td>
                        <!--  start Total Yr. 2026 for example -->

                        <!--  end Total Yr. 2026 for example -->
                      </template>
                    </tr>
                    <!-- end Equity Funding rates -->
                    <!-- start Equity Funding Value -->
                    <tr :data-repeater-style="true">
                      <td>
                        <div class="d-flex flex-column align-items-start">
                          <input
                            :value="'Equity Funding Value'"
                            :disabled="true"
                            class="form-control min-width-hover-300 text-left mt-3"
                            type="text" />

                          <i
                            style="visibility: hidden"
                            class="fa fa-ellipsis-h"></i>
                        </div>
                      </td>
                      <template
                        v-for="(dateFormatted, dateAsIndex) in ffeFundingDates"
                        :key="dateAsIndex">
                        <td>
                          <div class="d-flex flex-column align-items-center">
                            <InputNumber
                              v-model="
                                model.generalFixedAssetsFundingStructure.equity_funding_values[
                                  Number(dateAsIndex)
                                ]
                              "
                              :min="0"
                              disabled
                              input-class="text-center"
                              :minFractionDigits="0"
                              :maxFractionDigits="0"
                              suffix=" EGP"
                              fluid />
                            <!-- <i
                          @click="
                            handleRepeatRight(
                              model.loan_amounts.sub_items[leasingRevenueStreamBreakdownItem.id],
                              Number(dateAsIndex),
                            )
                          "
                          class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                          title="Repeat Right"></i> -->
                          </div>
                        </td>

                        <!--  end Total Yr. 2026 for example -->
                      </template>
                      <!-- Start Grand Total -->

                      <!-- End Grand Total -->
                    </tr>

                    <!-- end equity funding values -->

                    <!-- start new loan funding rate -->
                    <tr :data-repeater-style="true">
                      <td>
                        <div class="d-flex flex-column align-items-start">
                          <input
                            :value="'New Loans Funding Rate (%)'"
                            :disabled="true"
                            class="form-control min-width-hover-300 text-left mt-3"
                            type="text" />
                          <i
                            style="visibility: hidden"
                            class="fa fa-ellipsis-h"></i>
                        </div>
                      </td>
                      <template
                        v-for="(dateFormatted, dateAsIndex) in ffeFundingDates"
                        :key="dateAsIndex">
                        <td>
                          <!-- {{ logRender(leasingRevenueStreamBreakdownItem.id, dateAsIndex) }} -->
                          <div
                            class="d-flex min-w-percentage mx-auto mt-3 text-center flex-column align-items-center">
                            <InputNumber
                              v-model="
                                model.generalFixedAssetsFundingStructure.new_loans_funding_rates[
                                  dateAsIndex
                                ]
                              "
                              :min="0"
                              disabled
                              input-class="text-center"
                              :minFractionDigits="2"
                              :maxFractionDigits="2"
                              suffix=" %"
                              fluid />
                            <i
                              class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                              title="Repeat Right"
                              style="visibility: hidden"></i>
                          </div>
                        </td>
                        <!--  start Total Yr. 2026 for example -->

                        <!--  end Total Yr. 2026 for example -->
                      </template>
                    </tr>
                    <!-- end new loan Funding rates -->
                    <!-- start new loan Funding Value -->
                    <tr :data-repeater-style="true">
                      <td>
                        <div class="d-flex flex-column align-items-start mt-3">
                          <input
                            :value="'New Loans Funding Value'"
                            :disabled="true"
                            class="form-control min-width-hover-300 text-left"
                            type="text" />

                          <i
                            style="visibility: hidden"
                            class="fa fa-ellipsis-h"></i>
                        </div>
                      </td>
                      <template
                        v-for="(dateFormatted, dateAsIndex) in ffeFundingDates"
                        :key="dateAsIndex">
                        <td>
                          <div class="d-flex flex-column align-items-center mt-3">
                            <InputNumber
                              v-model="
                                model.generalFixedAssetsFundingStructure.new_loans_funding_values[
                                  dateAsIndex
                                ]
                              "
                              :min="0"
                              disabled
                              input-class="text-center"
                              :minFractionDigits="0"
                              :maxFractionDigits="0"
                              suffix=" EGP"
                              fluid />
                            <i
                              style="visibility: hidden"
                              class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                              title="Repeat Right"></i>
                          </div>
                        </td>
                      </template>
                      <!-- start Grand Total -->

                      <!-- end Grand Total -->
                    </tr>
                    <!-- end new loan funding values -->

                    <!-- start Loans Tenor ( Months ) -->
                    <tr :data-repeater-style="true">
                      <td>
                        <div class="d-flex flex-column align-items-start">
                          <input
                            :value="'Loans Tenor ( Months )'"
                            :disabled="true"
                            class="form-control min-width-hover-300 text-left mt-2"
                            type="text" />
                          <i
                            style="visibility: hidden"
                            class="fa fa-ellipsis-h"></i>
                        </div>
                      </td>
                      <template
                        v-for="(dateFormatted, dateAsIndex) in ffeFundingDates"
                        :key="dateAsIndex">
                        <td>
                          <div
                            class="d-flex min-w-percentage mx-auto mt-2 text-center flex-column align-items-center">
                            <InputNumber
                              v-model="model.generalFixedAssetsFundingStructure.tenors[dateAsIndex]"
                              :min="1"
                              :step="1"
                              input-class="text-center"
                              :minFractionDigits="0"
                              :maxFractionDigits="0"
                              suffix=" Mth"
                              fluid />
                            <i
                              @click="
                                handleRepeatRight(
                                  model.generalFixedAssetsFundingStructure.tenors,
                                  Number(dateAsIndex),
                                  ffeFundingDates,
                                )
                              "
                              class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                              title="Repeat Right"></i>
                          </div>
                        </td>
                        <!--  start Total Yr. 2026 for example -->

                        <!--  end Total Yr. 2026 for example -->
                      </template>
                    </tr>
                    <!-- end Loans Tenor ( Months ) -->

                    <!-- start Grace Period ( Months ) -->
                    <tr :data-repeater-style="true">
                      <td>
                        <div class="d-flex flex-column align-items-start">
                          <input
                            :value="'Grace Period ( Months )'"
                            :disabled="true"
                            class="form-control min-width-hover-300 text-left mt-2"
                            type="text" />
                          <i
                            style="visibility: hidden"
                            class="fa fa-ellipsis-h"></i>
                        </div>
                      </td>
                      <template
                        v-for="(dateFormatted, dateAsIndex) in ffeFundingDates"
                        :key="dateAsIndex">
                        <td>
                          <div
                            class="d-flex min-w-percentage mx-auto mt-2 text-center flex-column align-items-center">
                            <InputNumber
                              v-model="
                                model.generalFixedAssetsFundingStructure.grace_periods[dateAsIndex]
                              "
                              :min="0"
                              :max="
                                model.generalFixedAssetsFundingStructure.tenors[dateAsIndex] - 1
                              "
                              :step="1"
                              input-class="text-center"
                              :minFractionDigits="0"
                              :maxFractionDigits="0"
                              suffix=" Mth"
                              fluid />
                            <i
                              @click="
                                handleRepeatRight(
                                  model.generalFixedAssetsFundingStructure.grace_periods,
                                  Number(dateAsIndex),
                                  ffeFundingDates,
                                )
                              "
                              class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                              title="Repeat Right"></i>
                          </div>
                        </td>
                        <!--  start Total Yr. 2026 for example -->

                        <!--  end Total Yr. 2026 for example -->
                      </template>
                    </tr>
                    <!-- end Grace Period ( Months ) -->

                    <!-- start Interest Rate % -->
                    <tr :data-repeater-style="true">
                      <td>
                        <div class="d-flex flex-column align-items-start">
                          <input
                            :value="'Interest Rate %'"
                            :disabled="true"
                            class="form-control min-width-hover-300 text-left mt-2"
                            type="text" />
                          <i
                            style="visibility: hidden"
                            class="fa fa-ellipsis-h"></i>
                        </div>
                      </td>
                      <template
                        v-for="(dateFormatted, dateAsIndex) in ffeFundingDates"
                        :key="dateAsIndex">
                        <td>
                          <div
                            class="d-flex min-w-percentage mx-auto mt-2 text-center flex-column align-items-center">
                            <InputNumber
                              v-model="
                                model.generalFixedAssetsFundingStructure.interest_rates[dateAsIndex]
                              "
                              :min="0"
                              :max="100"
                              :step="0.25"
                              input-class="text-center"
                              :minFractionDigits="2"
                              :maxFractionDigits="5"
                              suffix=" %"
                              fluid />
                            <i
                              @click="
                                handleRepeatRight(
                                  model.generalFixedAssetsFundingStructure.interest_rates,
                                  Number(dateAsIndex),
                                  ffeFundingDates,
                                )
                              "
                              class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                              title="Repeat Right"></i>
                          </div>
                        </td>
                        <!--  start Total Yr. 2026 for example -->

                        <!--  end Total Yr. 2026 for example -->
                      </template>
                    </tr>
                    <!-- end Interest Rate % -->

                    <!-- start Installment Interval % -->
                    <tr :data-repeater-style="true">
                      <td>
                        <div class="d-flex flex-column align-items-start">
                          <input
                            :value="'Installment Interval'"
                            :disabled="true"
                            class="form-control min-width-hover-300 text-left mt-2"
                            type="text" />
                          <i
                            style="visibility: hidden"
                            class="fa fa-ellipsis-h"></i>
                        </div>
                      </td>
                      <template
                        v-for="(dateFormatted, dateAsIndex) in ffeFundingDates"
                        :key="dateAsIndex">
                        <td>
                          <div
                            class="d-flex flex-column mx-auto justify-content-center align-items-center">
                            <Select
                              filter
                              v-model="
                                model.generalFixedAssetsFundingStructure.installment_intervals[
                                  dateAsIndex
                                ]
                              "
                              :options="installmentIntervals"
                              optionLabel="title"
                              optionValue="id"
                              placeholder=""
                              checkmark
                              :highlightOnSelect="false"
                              :pt="{
                                label: {
                                  class:
                                    'd-flex font-weight-normal justify-content-center align-items-center',
                                },
                              }"
                              class="w-full" />
                            <i
                              @click="
                                handleRepeatRight(
                                  model.generalFixedAssetsFundingStructure.installment_intervals,
                                  Number(dateAsIndex),
                                  ffeFundingDates,
                                )
                              "
                              class="fa fa-ellipsis-h row-repeater-icon cursor-pointer"
                              title="Repeat Right"></i>
                          </div>
                        </td>
                        <!--  start Total Yr. 2026 for example -->

                        <!--  end Total Yr. 2026 for example -->
                      </template>
                    </tr>
                    <!-- end Installment Interval % -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- end one time expense -->
    <div class="row">
      <div class="col-md-12">
        <div
          class="d-flex align-items-center justify-content-end"
          style="gap: 5px">
          <button
            v-if="!isLoading"
            @click="submitForm"
            :disabled="disableSubmitBtn"
            data-button-value="save"
            type="submit"
            class="btn text-white active-style save-form">
            <!--  -->
            <span
              v-if="disableSubmitBtn && model.submit_button == 'save'"
              class="spinner-border mr-2 spinner-border-sm mb-1"
              data-button-value="save"
              role="status"
              aria-hidden="true"></span>
            <span
              class="text-lg"
              data-button-value="save"
              v-html="disableSubmitBtn && model.submit_button == 'save' ? 'Saving...' : 'Save'">
            </span>
          </button>

          <button
            v-if="!isLoading"
            @click="submitForm"
            :disabled="disableSubmitBtn"
            data-button-value="save-and-go-to-next-value"
            type="submit"
            class="btn text-white active-style save-form">
            <!--  -->
            <span
              v-if="disableSubmitBtn && model.submit_button == 'save-and-go-to-next-value'"
              class="spinner-border mr-2 spinner-border-sm mb-1"
              data-button-value="save-and-go-to-next-value"
              role="status"
              aria-hidden="true"></span>
            <span
              class="text-lg"
              data-button-value="save-and-go-to-next-value"
              v-html="
                disableSubmitBtn && model.submit_button == 'save-and-go-to-next-value'
                  ? 'Saving...'
                  : 'Save & Go To Next'
              ">
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import InputNumber, { InputNumberInputEvent } from 'primevue/inputnumber'
import MultiSelect from 'primevue/multiselect'
import RadioButton from 'primevue/radiobutton'
import Select from 'primevue/select'
import Loading from '../../../components/Common/Loading.vue'
// import VueLoadingTemplate from 'vue-loading-template';
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import Helper from '../../../Helpers/Helper'

import {
  datesInterface,
  IdTitleInterface,
  objectAsStringAndNumberInterface,
  objectAsStringAndNumberOrStringInterface,
  objectAsStringAndStringInterface,
} from '../../../Helpers/Type'
import Label from '../../../components/Form/Label.vue'
// import TextInput from "../Form/TextInput.vue";
const isLoading = ref<boolean>(true)
const installmentIntervals = computed<IdTitleInterface<string, string>[]>(() => {
  return Helper.getInstallmentInterest()
})

const lastMonthIndexInEachYear = ref<number[]>([])
interface selectsInterface {
  generalFixedAssetNames: IdTitleInterface<string, string>[]
  PerEmployeeFixedAssetNames: IdTitleInterface<string, string>[]
}
const selects = ref<selectsInterface>({} as selectsInterface)
interface hideTablesDatesInterface {
  ffe: number[]
}
const hideTablesDates = ref<hideTablesDatesInterface>({
  ffe: [],
})
const handleRepeatRight = (
  items: objectAsStringAndNumberOrStringInterface,
  dateAsIndex: number,
  dates: objectAsStringAndStringInterface,
) => {
  Helper.repeatRight(items, dateAsIndex, dates)
}
const hideOrExpandMyYear = (tableId: string, toDateAsIndex: number) => {
  const index = lastMonthIndexInEachYear.value.indexOf(toDateAsIndex)
  const fromDateAsIndex: number = lastMonthIndexInEachYear.value[index - 1] + 1 || 0
  const isCurrentDateExistInArray =
    hideTablesDates.value[tableId as keyof typeof hideTablesDates.value].includes(toDateAsIndex)
  for (let i = fromDateAsIndex; i <= toDateAsIndex; i++) {
    if (isCurrentDateExistInArray) {
      hideTablesDates.value[tableId as keyof typeof hideTablesDates.value] = hideTablesDates.value[
        tableId as keyof typeof hideTablesDates.value
      ].filter((i: number) => !(i >= fromDateAsIndex && i <= toDateAsIndex))
    } else {
      hideTablesDates.value[tableId as keyof typeof hideTablesDates.value].push(i)
    }
  }
}

const allTablesTotals = computed(() => {
  return {
    // حالة 1: array of objects مع nested key
    ffeTotals: Helper.calculateTableTotals(lastMonthIndexInEachYear, model.value.ffe, {
      nestedKey: 'ffe_counts',
    }),
  }
})
interface totalInterface {
  subRowTotals: {
    [key: string]: {
      per_year: {
        [key: string]: number
      }
      total: number
    }
  }
  totalPerColumns: {
    [key: string]: number
  }
  totalRowTotals: {
    per_year: {
      [key: string]: number
      total: number
    }
  }
}
const ffeTotals = computed<totalInterface>(() => allTablesTotals.value.ffeTotals)
const hasMicrofinance = ref<boolean>(false)
const currentActiveTab = ref('ffe')
const fixedAssetTypes = ref([])

const updatePositionsBasedOnDepartments = (item: PerEmployeeItem) => {
  item.position_ids = []
  item.filteredPositionsOptions = []
  item.department_ids.forEach((departmentId) => {
    item.filteredPositionsOptions.push(...positionsPerDepartments[departmentId])
  })
}

const showCustomPopup = (item: repeaterAbleType) => {
  if (item.payment_terms == 'customize') {
    currentActiveCollectionModal.value = item
  }
}
const calculatePaymentRatesTotal = (item: repeaterAbleType) => {
  const total: number = item.payment_rate.reduce((sum, rate) => {
    const numericValue: number = Number(rate) || 0
    return sum + numericValue
  }, 0)

  return total.toFixed(2)
}
interface InputNumberChangeEvent {
  originalEvent: KeyboardEvent
  value: number | null
  formattedValue: string
}
type repeaterAbleType = ffeItem | PerEmployeeItem
const handleRateChange = (
  item: repeaterAbleType,
  rowIndex: number,
  event: InputNumberInputEvent,
) => {
  item.payment_rate[rowIndex] = Helper.number_unformat(
    event.value, // in case of InputPercentage Field
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
const currentActiveCollectionModal = ref<repeaterAbleType | null>(null)
const collectionDueDays = Helper.getCollectionDays()
const paymentTerms = Helper.getFixedAssetPaymentTerms()
const depreciationDurations = Helper.getDepreciationDurations()
const replacementIntervals = Helper.getReplacementIntervals()
const disableSubmitBtn = ref(false)

interface ffeItem {
  name_id: number
  contingency_rate: number
  cost_annual_increase_rate: number
  depreciation_duration: number
  due_days: number[] | []
  payment_rate: number[] | []
  ffe_counts: {
    [key: string]: number
  }
  ffe_item_cost: number
  payment_terms: string
  replacement_cost_rate: number
  replacement_interval: number
  type: 'ffe'
}
interface newBranchItem {
  name_id: number
  contingency_rate: number
  cost_annual_increase_rate: number
  depreciation_duration: number
  due_days: number[] | []
  payment_rate: number[] | []
  counts: number
  ffe_item_cost: number
  payment_terms: string
  replacement_cost_rate: number
  replacement_interval: number
  type: 'new-branch'
}
interface PerEmployeeItem {
  name_id: number
  contingency_rate: number
  cost_annual_increase_rate: number
  depreciation_duration: number
  due_days: number[] | []
  payment_rate: number[] | []
  counts: number
  department_ids: IdTitleInterface<string, string>[]
  filteredPositionsOptions: IdTitleInterface<string, string>[]
  position_ids: IdTitleInterface<string, string>[]
  ffe_item_cost: number
  payment_terms: string
  replacement_cost_rate: number
  replacement_interval: number
  type: 'per-employee'
}
interface ffeGeneralStructureItem {
  equity_funding_rates: objectAsStringAndNumberInterface
  equity_funding_values: objectAsStringAndNumberInterface
  new_loans_funding_values: objectAsStringAndNumberInterface
  new_loans_funding_rates: objectAsStringAndNumberInterface
  fixed_asset_type: string
  grace_periods: objectAsStringAndNumberInterface
  installment_intervals: objectAsStringAndStringInterface
  interest_rates: objectAsStringAndNumberInterface
  tenors: objectAsStringAndNumberInterface
  is_fully_funded_though_equity: number //
}
type fixedAssetsInterface = {
  ffe: ffeItem[]
  generalFixedAssetsFundingStructure: ffeGeneralStructureItem
  'per-employee': PerEmployeeItem[]
  'new-branch': newBranchItem[]
  submit_button: string
  has_microfinance: boolean
}
const model = ref<fixedAssetsInterface>({} as fixedAssetsInterface)!
const departments = ref([])
let positionsPerDepartments = ref([])
const studyStartDate = ref(null)
const submitUrl = ref<string>('')
const dates = ref<datesInterface>({})
const ffeFundingDatesAndValues = computed(() => {
  let generalFundingStructure = model.value.generalFixedAssetsFundingStructure

  let uniqueDates: objectAsStringAndStringInterface = {}
  let ffeDirectAmounts: objectAsStringAndNumberInterface = {}

  if (model.value) {
    Object.keys(model.value?.ffe).forEach((index) => {
      var itemCost: number = model.value.ffe[index].ffe_item_cost || 0
      Object.keys(model.value.ffe[index].ffe_counts).forEach((dateAsIndex: string) => {
        let count: number = model.value.ffe[index].ffe_counts[dateAsIndex]
        if (count > 0 && !uniqueDates[dateAsIndex as keyof typeof uniqueDates]) {
          uniqueDates[dateAsIndex] = dates.value[dateAsIndex]
        }
        var currentCost: number = count * itemCost
        ffeDirectAmounts[dateAsIndex] = ffeDirectAmounts[dateAsIndex]
          ? ffeDirectAmounts[dateAsIndex] + currentCost
          : currentCost
      })
    })
    Object.keys(ffeDirectAmounts).forEach((dateAsIndex) => {
      var ffeAmount = ffeDirectAmounts[dateAsIndex] || 0
      var equityFundingRate = generalFundingStructure?.equity_funding_rates[dateAsIndex] || 0
      generalFundingStructure.equity_funding_values[dateAsIndex] =
        (ffeAmount * equityFundingRate) / 100
      generalFundingStructure.new_loans_funding_rates[dateAsIndex] = 100 - equityFundingRate
      generalFundingStructure.new_loans_funding_values[dateAsIndex] =
        ffeAmount - generalFundingStructure.equity_funding_values[dateAsIndex]
    })
  }
  model.value.ffe.ffe
  return {
    uniqueDates,
    ffeDirectAmounts,
  }
})
const ffeFundingDates = computed<objectAsStringAndStringInterface>(
  () => ffeFundingDatesAndValues.value.uniqueDates,
)
const ffeDirectAmounts = computed<objectAsStringAndNumberInterface>(
  () => ffeFundingDatesAndValues.value.ffeDirectAmounts,
)

const getModelData = () => {
  const body = document.querySelector('body') as HTMLBodyElement
  const csrfToken = body.dataset.token
  const baseUrl = body.dataset.baseUrl
  const companyId = body.dataset.currentCompanyId
  const studyId = body.dataset.studyId
  const lang = body.dataset.lang
  const fetchOldDataUrl = `${baseUrl}/${lang}/${companyId}/property-managements/study/${studyId}/fixed-assets-old-data`
  axios
    .get(fetchOldDataUrl, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
    })
    .then((response) => {
      studyStartDate.value = response.data.studyStartDate
      model.value = response.data.model
      hasMicrofinance.value = model.value.has_microfinance

      fixedAssetTypes.value = Helper.getFixedAssetTypes(response.data.has_microfinance)
      console.log(response.data.model)
      console.log(response.data.model)
      console.log(typeof response.data.model.ffe[0].contingency_rate)
      dates.value = Object.assign({}, response.data.dates)

      lastMonthIndexInEachYear.value = response.data.lastMonthIndexInEachYear
      empty_rows.value = response.data.empty_rows
      selects.value = response.data.selects
      //   expenseCategories.value = response.data.expenseCategories
      //   increaseYearsFormatted.value = response.data.increaseYearsFormatted
      //   revenueStreams.value = response.data.revenueStreams
      //   expenseNamesPerCategories = response.data.expenseNamesPerCategories
      //   revenueCategoriesPerRevenue = response.data.revenueCategoriesPerRevenue
      departments.value = response.data.departments
      positionsPerDepartments = response.data.positionsPerDepartments
      submitUrl.value = response.data.submitUrl
      isLoading.value = false
    })
    .catch((error) => {
      isLoading.value = false
      console.log(error)
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

const getYearsFromDates = computed<Record<string, string>>(() => {
  let result: Record<string, string> = {}
  Object.keys(dates.value).forEach((dateAsIndex: string) => {
    result[dateAsIndex] = dates.value[dateAsIndex].split("'").pop()!
  })
  return result
})

const empty_rows = ref({})

const addNewItem = <K extends keyof typeof model.value>(type: K) => {
  const emptyRow = empty_rows.value[type as keyof typeof empty_rows.value]
  return model.value[type].push({ ...emptyRow })
}
const deleteRepeaterRow = (index, type: keyof typeof model.value) => {
  model.value[type].splice(index, 1)
}

const submitForm = (e: Event) => {
  const submitBtn = e.target as HTMLButtonElement
  model.value.submit_button = submitBtn.getAttribute('data-button-value') as string
  disableSubmitBtn.value = true
  const body = document.querySelector('body') as HTMLBodyElement
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
      }).then(() => {
        if (response.data.redirectTo) {
          window.location.href = response.data.redirectTo
        }
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
  flex-shrink: 0;
  min-width: 140px;
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
.main-row-style {
  flex-wrap: nowrap;
}
</style>
<style scoped></style>
