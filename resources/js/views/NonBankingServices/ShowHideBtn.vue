<template>
    <div
        class="btn show-hide-style show-hide-repeater"
        :data-query="query"
        @click="toggleVisibility"
    >
        {{ buttonText }}
    </div>
</template>

<script>
export default {
    name: 'ShowHideBtn',
    props: {
        query: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            isVisible: true, // Tracks the toggle state
        };
    },
    computed: {
        buttonText() {
            // Simulate Laravel's __('Show/Hide') - update based on your translation approach
            return this.isVisible ? 'Hide' : 'Show';
        },
    },
    methods: {
        toggleVisibility() {
            this.isVisible = !this.isVisible;
            // Use jQuery to toggle the target element (matches your Blade setup)
            if (window.$) {
                window.$(this.query).toggle(this.isVisible);
            }
            // Emit the toggle state to the parent
            this.$emit('toggle', this.isVisible);
        },
    },
};
</script>

<style scoped>
/* Replicate the styling from your Blade component */
.btn {
    display: inline-block;
    padding: 6px 12px;
    font-size: 14px;
    font-weight: 400;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    cursor: pointer;
    border: 1px solid transparent;
    border-radius: 4px;
}

.show-hide-style {
    /* Add your custom styles here, e.g., from your CSS */
    background-color: #007bff; /* Example: Bootstrap primary color */
    color: white;
}

.show-hide-style:hover {
    background-color: #0056b3;
}
</style>
