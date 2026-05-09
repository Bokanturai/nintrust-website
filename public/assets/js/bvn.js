$("#verifyBVN").on("click", function (event) {
    event.preventDefault();

    let data = new FormData(this.form);
    let validationInfo = document.getElementById("validation-info");
    let download = document.getElementById("download");
    $("#errorMsg").hide();

    var preloader = $(".page-loading");

    function showLoader() {
        preloader.addClass("active").show();
    }

    function hideLoader() {
        preloader.removeClass("active");
        setTimeout(function () {
            preloader.hide();
        }, 1000);
    }

    $.ajax({
        type: "post",
        url: "/user/bvn-retrieve",
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
                validationInfo.innerHTML = `
<div class="border border-light p-3">
   <div class="row">
      <div class="col-md-4 text-center mb-3">
         <img class="rounded img-fluid" src="data:image/;base64, ${result.data.image || result.data.photo || ""}" alt="User Image" style="max-width: 100%; height: auto;">
      </div>
      <div class="col-md-8">
         <div class="table-responsive">
            <table class="table table-sm">
               <tbody>
                  <tr><th style="width: 40%;">BVN</th><td><span id="bvnno">${result.data.bvn || ""}</span></td></tr>
                  <tr><th>First Name</th><td>${result.data.firstName || ""}</td></tr>
                  <tr><th>Middle Name</th><td>${result.data.middleName || ""}</td></tr>
                  <tr><th>Last Name</th><td>${result.data.lastName || ""}</td></tr>
                  <tr><th>Date of Birth</th><td>${result.data.dateOfBirth || result.data.dob || "N/A"}</td></tr>
                  <tr><th>Phone No</th><td>${result.data.phoneNumber || result.data.phone || "N/A"}</td></tr>
                  <tr><th>Gender</th><td>${result.data.gender || "N/A"}</td></tr>
                  <tr><th>Enrollment Bank</th><td>${result.data.enrollmentBank || "N/A"}</td></tr>
                  <tr><th>Enrollment Branch</th><td>${result.data.enrollmentBranch || "N/A"}</td></tr>
                  <tr><th>Watchlisted</th><td>${result.data.watchListed || "N/A"}</td></tr>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>
`;
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
            setTimeout(function () {
                $("#errorMsg").fadeOut();
            }, 5000);
        },
    });
});

$("#freeSlip").on("click", function (event) {
    let getBVN = $("#bvnno").html();
    $.ajax({
        type: "get",
        url: "/user/standardBVN/" + getBVN,
        dataType: "json",
        processData: false,
        contentType: false,
        cache: false,
        success: function (response) {
            if (response.view) {
                var newWindow = window.open("", "_blank");
                newWindow.document.write(response.view);
                newWindow.document.close();
            } else {
                console.error("No view content received");
            }
        },
        error: function (data) {
            $.each(data.responseJSON.errors, function (key, value) {
                $("#errorMsg2").show();
                $("#message2").html(value);
            });
            setTimeout(function () {
                $("#errorMsg2").hide();
            }, 5000);
        },
    });
});

$("#paidSlip").on("click", function (event) {
    let getBVN = $("#bvnno").html();
    $.ajax({
        type: "get",
        url: "/user/premiumBVN/" + getBVN,
        dataType: "json",
        processData: false,
        contentType: false,
        cache: false,
        success: function (response) {
            if (response.view) {
                var newWindow = window.open("", "_blank");
                newWindow.document.write(response.view);
                newWindow.document.close();
            } else {
                console.error("No view content received");
            }
        },
        error: function (data) {
            $.each(data.responseJSON.errors, function (key, value) {
                $("#errorMsg2").show();
                $("#message2").html(value);
            });
            setTimeout(function () {
                $("#errorMsg2").hide();
            }, 5000);
        },
    });
});

$("#plasticSlip").on("click", function (event) {
    let getBVN = $("#bvnno").html();

    fetch("/user/plasticBVN/" + getBVN, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
        },
    })
        .then((response) => {
            if (response.ok) {
                const contentDisposition = response.headers.get("Content-Disposition");
                let filename = "document.pdf";
                if (contentDisposition && contentDisposition.indexOf("attachment") !== -1) {
                    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(contentDisposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, "");
                    }
                }
                return response.blob().then((blob) => ({ blob, filename }));
            } else {
                return response.json().then((data) => {
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
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            $.each(data.errors, function (key, value) {
                $("#errorMsg2").show();
                $("#message2").html(value);
            });
            setTimeout(function () {
                $("#errorMsg2").hide();
            }, 5000);
        });
});
