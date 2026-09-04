/* ============================================================
   TechOS — Comportamentos da página de FAQ
   ============================================================ */
(async function () {
  "use strict";

  const faqList = document.querySelector("[data-faq-list]");
  if (!faqList) {
    return;
  }

  const faqs = await fetch(`../../../api/faqs/list`).then(res => res.json());
  if (faqs.status == "error") {
    faqList.innerHTML = `
      <article class="faq-item">
        ${faqs.message}      
      </article>
    `;
  } else {
    faqs.data.forEach(faq => {
      faqList.innerHTML += `
      <article class="faq-item">
            <details>
              <summary>${faq.question}</summary>
              <p>${faq.answer}</p>
            </details>
          </article>
    `;
    });
  }

  const details = Array.from(faqList.querySelectorAll("details"));

  details.forEach(function (item) {
    item.addEventListener("toggle", function () {
      if (!item.open) {
        return;
      }

      details.forEach(function (other) {
        if (other !== item && other.open) {
          other.open = false;
        }
      });
    });
  });
})();
