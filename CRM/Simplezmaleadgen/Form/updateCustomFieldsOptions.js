document.addEventListener("DOMContentLoaded", function () {
  // Wait for the DOM content to load
  var customGroupSelect = document.getElementById("custom_group");
  if (customGroupSelect) {
    // Add event listener for the change event on the custom group select field
    customGroupSelect.addEventListener("change", function () {
      var customGroupId = this.value; // Get the selected custom group ID

      // Make an AJAX call to retrieve custom fields options based on the selected custom group ID
      CRM.api4("CustomField", "get", {
        where: [["custom_group_id", "=", customGroupId]],
      }).then(
        function (customFields) {
          // Update the options of the custom fields select field
          var customFieldsSelect = document.getElementById("custom_field");
          customFieldsSelect.innerHTML = ""; // Clear existing options

          // Add "Please Select" as the first option
          var pleaseSelectOption = document.createElement("option");
          pleaseSelectOption.value = "";
          pleaseSelectOption.textContent = "- Please Select -";
          customFieldsSelect.appendChild(pleaseSelectOption);

          customFields.forEach(function (customField) {
            var optionElement = document.createElement("option");
            optionElement.value = customField.id;
            optionElement.textContent = customField.label;
            customFieldsSelect.appendChild(optionElement);
          });
          jQuery(customFieldsSelect).trigger("change"); // Trigger change event for compatibility with select2
        },
        function (failure) {
          // Handle failure
          console.error("Failed to fetch custom fields:", failure);
        }
      );
    });
    // Manually trigger change event on custom group select to load custom fields on initial page load
    customGroupSelect.dispatchEvent(new Event("change"));
  }
});
