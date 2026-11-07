import Faq from "../../_common/classes/Faq.js";

const faqService = new Faq();
const faqList = document.querySelector("[data-faq-list]");

function renderFaqs(faqs) {
  faqList.replaceChildren();
  if (!faqs.length) {
    const emptyState = document.createElement("p");
    emptyState.className = "faq-empty-state";
    emptyState.textContent = "Nenhuma pergunta frequente cadastrada.";
    faqList.appendChild(emptyState);
    return;
  }

  const details = [];
  faqs.forEach(faq => {
    const article = document.createElement("article");
    article.className = "faq-item";
    const disclosure = document.createElement("details");
    const summary = document.createElement("summary");
    const answer = document.createElement("p");

    summary.textContent = faq.question || "Pergunta sem título";
    answer.textContent = faq.answer || "Resposta indisponível.";
    disclosure.append(summary, answer);
    article.appendChild(disclosure);
    faqList.appendChild(article);
    details.push(disclosure);
  });

  details.forEach(item => item.addEventListener("toggle", () => {
    if (!item.open) return;
    details.forEach(other => {
      if (other !== item && other.open) other.open = false;
    });
  }));
}

async function loadFaqs() {
  if (!faqList) return;
  try {
    const response = await faqService.listAll();
    if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
      throw new Error(response.message || "Não foi possível carregar as perguntas frequentes.");
    }
    const faqs = Array.isArray(response?.data) ? response.data : [];
    renderFaqs(faqs);
  } catch (error) {
    console.error("Erro ao carregar FAQs:", error);
    faqList.replaceChildren();
    const errorMessage = document.createElement("p");
    errorMessage.className = "faq-error-message";
    errorMessage.textContent = "Não foi possível carregar as perguntas frequentes. Tente novamente mais tarde.";
    faqList.appendChild(errorMessage);
  }
}

loadFaqs();
