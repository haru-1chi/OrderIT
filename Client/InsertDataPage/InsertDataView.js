export default class InsertDataView {
  constructor(model) {
    this.model = model;
    this.mainContainer = document.getElementById("main-container");

    this.selectPageButton = document.querySelectorAll("#select-page-button");
    this.allUpdateFormButton = document.querySelectorAll("#update-form-submit");
    this.allMainPages = document.querySelectorAll("#main-page");
    this.selectMainPageButton = document.querySelectorAll(
      "#select-main-page-button"
    );

    this.allForms = document.querySelectorAll("#page-form");

    this.currentPageElement;
    this.currentMainPageElement;
    this.currentActiveMainPage;

    console.log("init view");
    console.log(this.selectPageButton);

    this.#setupPages();
  }

  #getPageElementByPageId(pageId) {
    const allPages =
      this.currentMainPageElement.querySelectorAll("#page-column");
    for (const column of allPages) {
      if (column.dataset.pageid == pageId) {
        return column;
      }
    }
    return null;
  }

  #getMainPageElementByMainPageId(mainPageId) {
    for (const mainPage of this.allMainPages) {
      if (mainPage.dataset.mainpageid == mainPageId) {
        return mainPage;
      }
    }

    return nil;
  }

  #disableAllPages() {
    const allPages =
      this.currentMainPageElement.querySelectorAll("#page-column");

    allPages.forEach((column) => {
      column.style.display = "none";
    });
  }

  #disableAllMainPages() {
    this.allMainPages.forEach((mainPage) => {
      mainPage.style.display = "none";
    });
  }

  #setupPages() {
    this.#disableAllMainPages();
  }

  loadPage(pageId) {
    if (!pageId) {
      console.error("Page id doesn't set yet!!.");
      return false;
    }
    const pageElement = this.#getPageElementByPageId(pageId);

    if (!pageElement) {
      console.error(`Cannot get ${pageId} page element.`);
      return false;
    }
    if (this.currentPageElement) {
      this.currentPageElement.style.display = "none";
    }

    pageElement.style.display = "block";

    this.currentPageElement = pageElement;
  }

  getSelectMainPageButton(mainPageId) {
    for (const selectButton of this.selectMainPageButton) {
      if (selectButton.dataset.selectmainpage == mainPageId) {
        return selectButton;
      }
    }
  }

  setMainPageActiveButton(mainPageId) {
    const selectButton = this.getSelectMainPageButton(mainPageId)

    if (!selectButton) {
      console.error("Cannot get select main page button.")
      return false
    }

    if (this.currentActiveMainPage) {
      this.currentActiveMainPage.className = "default-main-page-button"
    }

    selectButton.className = "active-main-page-button"
    this.currentActiveMainPage = selectButton

    console.log("select button:", selectButton)
  }

  loadMainPage(mainPageId, pageId) {
    const mainPageElement = this.#getMainPageElementByMainPageId(mainPageId);

    if (this.currentMainPageElement) {
      this.currentMainPageElement.style.display = "none";
    }

    this.currentMainPageElement = mainPageElement;
    mainPageElement.style.display = "block";

    this.#disableAllPages();
    this.loadPage(pageId);
  }

  bindSelectPageButtonClicked(callback) {
    this.selectPageButton.forEach((selectPageElement) => {
      const defaultId = selectPageElement.value;
      selectPageElement.addEventListener("change", (event) => {
        callback(event);
        selectPageElement.value = defaultId;

        const selectedOption =
          selectPageElement.querySelector(`option[selected]`);

        if (!selectedOption.value == defaultId) {
          selectedOption.selected = false;
        }

        const mainOption = selectPageElement.querySelector(
          `option[value="${defaultId}"]`
        );
        console.log("Main option:");
        if (!mainOption) {
          console.log("Cannot get main option.");
          return false;
        }
        mainOption.selected = true;
      });
    });
  }

  bindUpdateFormSubmit(callback) {
    this.allUpdateFormButton.forEach((updateButtonElement) => {
      const formId = updateButtonElement.dataset.target;

      const form = document.querySelector(`#${formId}`);

      updateButtonElement.addEventListener("click", (event) => {
        callback(form, updateButtonElement, event);
      });
    });
  }

  bindSelectMainPageButtonClicked(callback) {
    this.selectMainPageButton.forEach((mainPageButton) => {
      mainPageButton.addEventListener("click", callback);
    });
  }

  bindFormSubmit(callback) {
    // console.log("all forms:", this.allForms)

    this.allForms.forEach((form) => {
      const submitButton = form.querySelector("#form-submit");
      submitButton.addEventListener("click", (event) => {
        callback(form, submitButton, event);
      });
    });
  }
}
