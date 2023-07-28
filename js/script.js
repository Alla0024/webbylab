document.addEventListener("DOMContentLoaded", function () {
    const pagination = document.querySelector(".pagination");
    if (!pagination) return;

    pagination.addEventListener("click", function (event) {
        event.preventDefault();
        const target = event.target;

        if (target.tagName === "A" && !target.parentElement.classList.contains("disabled")) {
            const page = target.innerText;
            const currentUrl = new URL(window.location.href);
            const searchParams = new URLSearchParams(currentUrl.search);
            searchParams.set('page', page);
            currentUrl.search = searchParams.toString();
            window.location.href = currentUrl.href;
        }
    });
});
