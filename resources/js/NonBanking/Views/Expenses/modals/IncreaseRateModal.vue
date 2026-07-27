<template>
  <slot name="default"></slot>
  <div
    class="modal fade show"
    v-if="modals.increaseRate.currentActive == typeObject.id + '-' + index"
    @click.self="modals.increaseRate.currentActive = null"
    tabindex="-1"
    role="dialog"
    aria-labelledby="exampleModalCenterTitle"
    style="display: block; padding-right: 17px"
    aria-modal="true">
    <div
      class="modal-dialog modal-md modal-dialog-centered"
      role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-blue">Annual Increase Rate</h5>
          <button
            type="button"
            class="close"
            @click="modals.increaseRate.currentActive = null">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="customize-elements">
            <table class="table">
              <thead>
                <tr>
                  <th class="text-center">Year</th>
                  <th class="text-center">Rate %</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(yearFormatted, index) in increaseYearsFormatted"
                  :key="index">
                  <td>
                    <div class="max-w-selector-popup">
                      <input
                        readonly=""
                        class="form-control"
                        :value="yearFormatted"
                        :placeholder="'Year ' + (index + 1)" />
                    </div>
                  </td>

                  <td>
                    <div class="max-w-selector-popup">
                      <InputNumber
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        :step="0.25"
                        :min="0"
                        :max="100"
                        mode="decimal"
                        showButtons
                        v-model="item.increase_rates[index]"
                        suffix=" %"
                        fluid />
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn save-modal btn-primary"
            @click="modals.increaseRate.currentActive = null"
            data-dismiss="modal">
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import InputNumber from 'primevue/inputnumber'
defineProps({
  index: {
    required: true,
    type: Number,
  },
  typeObject: {
    required: true,
    type: Object,
  },
  increaseYearsFormatted: {
    required: true,
    type: Array,
  },
  item: {
    required: true,
    type: Object,
  },
  modals: {
    type: Object,
    required: true,
  },
})
</script>
