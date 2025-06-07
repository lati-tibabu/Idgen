function choose_avatar(){
    old_image.style.display = "none";
    new_image.style.display = "block";
}

old_image = document.getElementById("avatar_image");
new_image = document.getElementById("avatar_image2");

image = document.getElementById("image");

image.addEventListener('change', function() {
    // Check if a file is selected
    if (image.files.length > 0) {
        // Get the selected file
        const file = image.files[0];

        const reader = new FileReader();

        // Set up a function to run when the reader loads the file
        reader.onload = function(e) {
            // Update the image element's src attribute with the data URL
            new_image.src = e.target.result;
        };

        // Read the file as a data URL
        reader.readAsDataURL(file);

        // Update the paragraph with the directory and file name
        // new_selected_image.innerHTML = `Directory: ${file.webkitRelativePath} File Name: ${file.name}`;
    } else {
        // Clear the paragraph if no file is selected
        // new_selected_image.textContent = '';
        imagePreview.src = '';
    }
});