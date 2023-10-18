/**
 * 
 * @param {MouseEvent} event 
 */
const toggleFieldset = (event)=>
{
    /**
     * @type {HTMLFieldSetElement}
     */
    const fieldset = event.target.parentElement;
    const collapsed = fieldset.getAttribute("collapsed") === "true";
    fieldset.setAttribute("collapsed", !collapsed);
};

/**
 * Add click event to legend.
 * 
 * @param {HTMLFieldSetElement} fieldset 
 */
const setupEventListener = (fieldset)=>
    fieldset.firstElementChild.addEventListener("click", toggleFieldset);

const initialize = ()=>
{
    const form = document.querySelector("#wp-api-caching-options");
    if (!form)
        return;

    form.querySelectorAll("fieldset")
        .forEach(setupEventListener);
};

window.addEventListener("DOMContentLoaded", initialize);