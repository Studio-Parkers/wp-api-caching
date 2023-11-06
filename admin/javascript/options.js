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

const toggleRelatedPosts = (event)=>
{
    const container = event.target.nextElementSibling.nextElementSibling;
    container.style.display = event.target.checked ? "block" : "none";
};

const removeRelation = (event)=>
{
    const row = event.target.parentElement;
    row.parentElement.removeChild(row);
};

/**
 * 
 * @param {InputEvent} event 
 */
const updateIDDropdown = (event)=>
{
    const idDropdown = event.target.nextElementSibling;
    idDropdown.innerHTML = "";

    const allPosts = [{ID: -1, post_title: "Any"}, ...posts[event.target.value]];
    allPosts.forEach(post=>
    {
        const option = document.createElement("option");
        option.value = post.ID;
        option.innerHTML = post.post_title;
        if (post.ID >= 0)
            option.innerHTML = `${option.innerHTML} - ID ${post.ID}`;
        
        idDropdown.appendChild(option);
    });
};

const resetDropdowns = (newSelector = null)=>
{    
    const typeSelectors = newSelector ? [newSelector] : document.querySelectorAll("select[name$=\"_relations[]\"]");
    typeSelectors.forEach(select=>
    {
        const selected = select.value;
        const selectedRelation = select.nextElementSibling.value;

        // Stupid hack to make sure even is only added once
        select.removeEventListener("change", updateIDDropdown);
        select.addEventListener("change", updateIDDropdown);
        select.dispatchEvent(new Event("change"));

        select.value = selected;

        if (selectedRelation)
            select.nextElementSibling.value = selectedRelation;
    });
};

const addRelation = (event)=>
{
    const parent = event.target.parentElement;

    const container = document.createElement("div");
    container.className = "post-type-row";
    parent.appendChild(container);

    const select = document.createElement("select");
    select.name = `${parent.getAttribute("data-hash")}_relations[]`;
    container.appendChild(select);
    postTypes.forEach(postType=>
    {
        const option = document.createElement("option");
        option.innerHTML = postType;
        select.appendChild(option);
    });

    const selectID = document.createElement("select");
    selectID.name = `${parent.getAttribute("data-hash")}_related_posts[]`;
    container.appendChild(selectID);

    const btn = document.createElement("button");
    btn.type = "button";
    btn.innerHTML = "remove";
    btn.addEventListener("click", removeRelation);
    container.appendChild(btn);

    resetDropdowns(select);
};

/**
 * Add click event to legend.
 * 
 * @param {HTMLFieldSetElement} fieldset 
 */
const setupEventListener = (fieldset)=>
{
    fieldset.firstElementChild.addEventListener("click", toggleFieldset);
    fieldset.querySelectorAll("input[type='checkbox']").forEach(input=>
    {
        const relatedContainer = input.nextElementSibling.nextElementSibling;
        relatedContainer.setAttribute("collapsed", true);
        relatedContainer.querySelector("button").addEventListener("click", addRelation);
        
        input.addEventListener("change", toggleRelatedPosts);
        input.dispatchEvent(new Event("change"));
    });

    fieldset.querySelectorAll("button[name=\"remove-relation-btn\"]")?.forEach(btn=> btn.addEventListener("click", removeRelation));
};

const initialize = ()=>
{
    const form = document.querySelector("#wp-api-caching-options");
    if (!form)
        return;

    form.querySelectorAll("fieldset")
        .forEach(setupEventListener);

    resetDropdowns();
};

window.addEventListener("DOMContentLoaded", initialize);