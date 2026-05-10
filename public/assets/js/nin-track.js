$("#verifyNIN").on("click", function (event) {
    // Stop the button from submitting the form:
    event.preventDefault();

    let data = new FormData(this.form);
    let validationInfo = document.getElementById("validation-info");
    let download = document.getElementById("download");
    $("#errorMsg").hide();

    var preloader = $('.page-loading');

    function showLoader() {
        preloader.addClass('active').show();
    }

    function hideLoader() {
        preloader.removeClass('active');
        setTimeout(function () {
            preloader.hide();
        }, 1000);
    }

    $.ajax({
        type: "post",
        url: "/user/nin-track-retrieve",
        dataType: "json",
        data,
        processData: false,
        contentType: false,
        cache: false,
        beforeSend: function () {
            showLoader();
            $("#download").hide();
        },
        success: function (result) {
            $("#loader").hide();

            if (result && result.data) {
                const photoData = result.data.face || result.data.photo || result.data.image || result.data.passport || '';
                const defaultPhoto = '/assets/images/img/default-avatar.jpg';
                const photoSrc = photoData
                    ? (photoData.startsWith('data:image') ? photoData : `data:image/;base64,${photoData}`)
                    : defaultPhoto;

                validationInfo.innerHTML = `
            <div class="border border-light">
   <div class="table-responsive">
   <center><span class="text-danger mt-4" style="margin-top:5px; padding-top:3px;">${result.data.message || ""}</span></center>
      <table class="table">
         <thead >
            <tr>
               <th style="border: none ! important;" width="20%"></th>
               <th style="border: none ! important;"></th>
               <th style="border: none ! important;"></th>
               <th style="border: none ! important;"></th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <th scope="row" rowspan="9">
                  <img class="rounded" src="${photoSrc}" alt="User Image" style="width: 250px; height: 250px;">
               </th>
            </tr>
              <tr>
               <th scope="row" style="text-align:right; border: none ! important;">Tracking Number</th>
               <td  style="text-align:left">${result.data.trackingid || result.data.trackingId || "N/A"}
               </td>
            </tr>
            <tr>
               <th scope="row" style="text-align:right; border: none ! important;">NIN</th>
               <td style="text-align:left" ><span id="nin_no" >${result.data.nin || result.data.idNumber || ""}</span>
               </td>
            </tr>
            <tr>
               <th scope="row" style="text-align:right; border: none ! important;">FirstName</th>
               <td  style="text-align:left">${result.data.firstname || result.data.firstName || ""}
               </td>
            </tr>
            <tr>
               <th scope="row" style="text-align:right; border: none ! important;">Surname</th>
               <td  style="text-align:left">${result.data.lastname || result.data.surname || result.data.lastName || ""}
               </td>
            </tr>
            <tr>
               <th scope="row" style="text-align:right; border: none ! important;">Middle Name</th>
               <td  style="text-align:left">${result.data.middlename || result.data.middleName || ""}
               </td>
            </tr>
            <tr>
                <th scope="row" style="text-align:right; border: none !important;">Gender</th>
                <td style="text-align:left">
                    ${result.data.gender === 'm' || result.data.gender === 'Male' ? 'Male' : (result.data.gender === 'f' || result.data.gender === 'Female' ? 'Female' : 'Not Specified')}
                </td>
            </tr>

            <tr>
               <th scope="row" style="text-align:right;">Address</th>
               <td  style="text-align:left">${result.data.address || result.data.addressLine || "N/A"}
               </td>
            </tr>
         </tbody>
      </table>
   </div>
</div>
            `;
                hideLoader();
                $("#validation-info").removeClass("hidden").removeClass("d-none");
                $("#download").show();
                $("#downloadDiv").show();

            } else {
                hideLoader();
                $("#errorMsg").show();
                $("#message").html("Invalid Response");

                setTimeout(function () {
                    $("#errorMsg").fadeOut();
                }, 30000);
            }
        },
        error: function (data) {
            hideLoader();
            $.each(data.responseJSON.errors, function (key, value) {
                $("#errorMsg").show();
                $("#message").html(value);
            });
            setTimeout(function () {
                $("#errorMsg").fadeOut();
            }, 30000);
        },
    });
});

$("#regularSlip").on("click", function (event) {
    let getNIN = $("#nin_no").html();

    fetch("/user/regularSlip/" + getNIN, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
        },
    })
        .then((response) => {
            if (response.ok) {
                // Extract filename from Content-Disposition header
                const contentDisposition = response.headers.get(
                    "Content-Disposition"
                );
                let filename = "document.pdf";
                if (
                    contentDisposition &&
                    contentDisposition.indexOf("attachment") !== -1
                ) {
                    const filenameRegex =
                        /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(contentDisposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, "");
                    }
                }
                return response.blob().then((blob) => ({ blob, filename }));
            } else {
                return response.json().then((data) => {
                    // Handle errors
                    $.each(data.errors, function (key, value) {
                        $("#errorMsg2").show();
                        $("#message2").html(value);
                    });
                    setTimeout(function () {
                        $("#errorMsg2").hide();
                    }, 5000);
                });
            }
        })
        .then(({ blob, filename }) => {
            if (blob) {
                // Create a link element, use it to download the blob with the extracted filename
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = filename; // Use the extracted filename
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            // Handle errors
            $.each(data.errors, function (key, value) {
                $("#errorMsg2").show();
                $("#message2").html(value);
            });
            setTimeout(function () {
                $("#errorMsg2").hide();
            }, 5000);
        });
});

$("#vninSlip").on("click", function (event) {
    let getNIN = $("#nin_no").html();

    fetch("/user/vninSlip/" + getNIN, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
        },
    })
        .then((response) => {
            if (response.ok) {
                // Extract filename from Content-Disposition header
                const contentDisposition = response.headers.get(
                    "Content-Disposition"
                );
                let filename = "document.pdf"; // Default filename if not found in headers
                if (
                    contentDisposition &&
                    contentDisposition.indexOf("attachment") !== -1
                ) {
                    const filenameRegex =
                        /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(contentDisposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, "");
                    }
                }
                return response.blob().then((blob) => ({ blob, filename }));
            } else {
                return response.json().then((data) => {
                    // Handle errors
                    $.each(data.errors, function (key, value) {
                        $("#errorMsg2").show();
                        $("#message2").html(value);
                    });
                    setTimeout(function () {
                        $("#errorMsg2").hide();
                    }, 5000);
                });
            }
        })
        .then(({ blob, filename }) => {
            if (blob) {
                // Create a link element, use it to download the blob with the extracted filename
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = filename; // Use the extracted filename
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            // Handle errors
            $.each(data.errors, function (key, value) {
                $("#errorMsg2").show();
                $("#message2").html(value);
            });
            setTimeout(function () {
                $("#errorMsg2").hide();
            }, 5000);
        });
});


