$("#verifyNIN").on("click", function (event) {
    event.preventDefault();
    let data = new FormData(this.form);
    let validationInfo = document.getElementById("validation-info");
    let download = document.getElementById("download");
    $("#errorMsg").hide();
    var preloader = $('.page-loading');
    function showLoader() { preloader.addClass('active').show(); }
    function hideLoader() { preloader.removeClass('active'); setTimeout(function () { preloader.hide(); }, 1000); }
    $.ajax({
        type: "post",
        url: "/user/nin-retrieve",
        dataType: "json",
        data,
        processData: false,
        contentType: false,
        cache: false,
        beforeSend: function () { showLoader(); $("#download").hide(); },
        success: function (result) {
            $("#loader").hide();
            if (result && result.data) {
                const photoData = result.data.photo || result.data.face || result.data.image || result.data.passport || '';
                const defaultPhoto = '/assets/images/img/default-avatar.jpg';
                const photoSrc = photoData
                    ? (photoData.startsWith('data:image') ? photoData : `data:image/;base64,${photoData}`)
                    : defaultPhoto;

                validationInfo.innerHTML = `
<div class="border border-light p-3">
   <div class="row">
      <div class="col-md-4 text-center mb-3">
         <img class="rounded img-fluid" src="${photoSrc}" alt="User Image" style="max-width: 100%; height: auto;">
      </div>
      <div class="col-md-8">
         <div class="table-responsive">
            <table class="table table-sm">
               <tbody>
                  <tr><th style="width: 40%;">NIN</th><td><span id="nin_no">${result.data.nin || result.data.idNumber || ""}</span></td></tr>
                  <tr><th>First Name</th><td>${result.data.firstname || result.data.firstName || ""}</td></tr>
                  <tr><th>Middle Name</th><td>${result.data.middlename || result.data.middleName || ""}</td></tr>
                  <tr><th>Surname</th><td>${result.data.surname || result.data.lastName || ""}</td></tr>
                  <tr><th>Date of Birth</th><td>${result.data.birthdate || result.data.birthDate || result.data.dateOfBirth || "N/A"}</td></tr>
                  <tr><th>Phone No</th><td>${result.data.telephoneno || result.data.telephoneNo || result.data.mobile || result.data.phone || "N/A"}</td></tr>
                  <tr><th>Gender</th><td>${result.data.gender || "N/A"}</td></tr>
                  <tr><th>Residence</th><td>${result.data.residence_state || ""} - ${result.data.residence_lga || ""}</td></tr>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>
`;
                hideLoader();
                $("#validation-info").removeClass("hidden").removeClass("d-none");
                $("#download").show();
            } else {
                hideLoader();
                $("#errorMsg").show();
                $("#message").html("Invalid Response");
            }
        },
        error: function (data) {
            hideLoader();
            $.each(data.responseJSON.errors, function (key, value) {
                $("#errorMsg").show();
                $("#message").html(value);
            });
            setTimeout(function () { $("#errorMsg").fadeOut(); }, 30000);
        },
    });
});

function downloadSlip(url, getNIN) {
    fetch(url + getNIN, {
        method: "GET",
        headers: { 
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
        },
    })
    .then((response) => {
        if (response.ok) {
            const contentDisposition = response.headers.get("Content-Disposition");
            let filename = "document.pdf";
            if (contentDisposition && contentDisposition.indexOf("attachment") !== -1) {
                const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                const matches = filenameRegex.exec(contentDisposition);
                if (matches != null && matches[1]) { filename = matches[1].replace(/['"]/g, ""); }
            }
            return response.blob().then((blob) => ({ blob, filename }));
        } else {
            return response.json().then((data) => {
                $.each(data.errors, function (key, value) {
                    $("#errorMsg2").show(); $("#message2").html(value);
                });
                setTimeout(function () { $("#errorMsg2").hide(); }, 5000);
            });
        }
    })
    .then(({ blob, filename }) => {
        if (blob) {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url; a.download = filename;
            document.body.appendChild(a); a.click(); a.remove();
            window.URL.revokeObjectURL(url);
        }
    });
}

$("#regularSlip").on("click", function () { downloadSlip("/user/regularSlip/", $("#nin_no").html()); });
$("#standardSlip").on("click", function () { downloadSlip("/user/standardSlip/", $("#nin_no").html()); });
$("#premiumSlip").on("click", function () { downloadSlip("/user/premiumSlip/", $("#nin_no").html()); });
$("#vninSlip").on("click", function () { downloadSlip("/user/vninSlip/", $("#nin_no").html()); });
