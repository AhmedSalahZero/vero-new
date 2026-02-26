@csrf
            
<input type="hidden" name="model_id" value="{{ $model->id ?? 0  }}">
            <input type="hidden" name="company_id" value="{{ getCurrentCompanyId()  }}">
            <input type="hidden" name="model_name" value="Study">
            <input type="hidden" name="expense_type" value="{{ $expenseType }}">
            <input type="hidden" name="study_id" id="study-id-js" value="{{ $study->id }}">
            <input type="hidden" id="study-start-date" value="{{ $study->getStudyStartDate() }}">
            <input type="hidden" id="study-end-date" value="{{ $study->getStudyEndDate() }}">
