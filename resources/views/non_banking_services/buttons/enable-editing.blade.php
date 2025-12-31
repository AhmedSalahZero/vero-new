				@if($inEditMode)
					 <button  
					 	data-enable-edit-text="{{ __('Enable Edit') }}"
					 	data-disable-edit-text="{{ __('Disable Edit') }}"
					    data-is-enable-editing="0" id="enable-editing-btn" in-edit-mode="{{ $inEditMode }}" class="btn active-style ">
						 {{ __('Enable Edit') }}
					 </button>
					 @endif
