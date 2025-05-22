/**
 * Supplier Application Form JavaScript
 */
$(document).ready(function() {
    // Initialize file upload plugins
    if ($('.dropify').length) {
        $('.dropify').dropify({
            messages: {
                'default': 'Drag and drop a file here or click',
                'replace': 'Drag and drop or click to replace',
                'remove': 'Remove',
                'error': 'Oops, something wrong happened.'
            }
        });
    }
    
    // Initialize Dropzone
    Dropzone.autoDiscover = false;
    
    // Only initialize Dropzone if the element exists
    if ($("#store-photos-dropzone").length) {
        var myDropzone = new Dropzone("#store-photos-dropzone", {
            url: window.location.href,
            autoProcessQueue: false,
            paramName: "store_photos",
            uploadMultiple: true,
            parallelUploads: 5,
            maxFiles: 5,
            maxFilesize: 5, // MB
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            init: function() {
                var dz = this;
                
                // Hook into the form submission
                $("#supplier-application-form").on("submit", function(e) {
                    // If there are files in the dropzone, process them
                    if (dz.getQueuedFiles().length > 0) {
                        e.preventDefault();
                        dz.processQueue();
                    }
                });
                
                // Handle form submission with files
                this.on("sendingmultiple", function(files, xhr, formData) {
                    // Get all form data
                    var formElements = $("#supplier-application-form").serializeArray();
                    $.each(formElements, function(i, field) {
                        formData.append(field.name, field.value);
                    });
                });
                
                this.on("successmultiple", function(files, response) {
                    // Look for JSON response
                    try {
                        var jsonResponse = JSON.parse(response);
                        if (jsonResponse.success) {
                            window.location.href = "application-success.php";
                        } else if (jsonResponse.error) {
                            showError(jsonResponse.error);
                        }
                    } catch (e) {
                        // If response redirects to success page
                        if (response.indexOf("Application Submitted Successfully") > -1) {
                            window.location.href = "application-success.php";
                        } else {
                            // Check if there's an error message in the HTML
                            var errorMsg = $(response).find(".alert-danger").text();
                            if (errorMsg) {
                                showError(errorMsg);
                            } else {
                                showError("Error processing your application. Please try again.");
                            }
                        }
                    }
                });
                
                this.on("errormultiple", function(files, response) {
                    showError("Error uploading files. Please try again.");
                });
            }
        });
    }
    
    // Function to show error message
    function showError(message) {
        var errorHtml = '<div class="alert alert-danger" role="alert">' +
                        '<i class="mdi mdi-block-helper me-2"></i> ' + message +
                        '</div>';
        
        // Check if error container exists, if not create it
        if ($("#error-container").length === 0) {
            $(".col-lg-8").prepend('<div id="error-container"></div>');
        }
        
        $("#error-container").html(errorHtml);
        $('html, body').animate({
            scrollTop: $("#error-container").offset().top - 100
        }, 500);
    }
    
    // Adding form ID for JavaScript reference
    $("form").attr("id", "supplier-application-form");
});
