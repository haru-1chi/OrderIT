export default class InsertDataModel {
  constructor() {
    console.log("init model");
    this.currentUrl = new URL(window.location.href);

  }

  submitForm(form, submitButton) {
    console.log("Form:", form)
    form.requestSubmit(submitButton)
  }

  getMainPageGETData() {
    return this.currentUrl.searchParams.get("mainPage")
  }

  getPageGETData() {
    return this.currentUrl.searchParams.get("page")
  }
  
  setMainPageUrl(mainPage, page) {
    this.currentUrl.searchParams.set("mainPage", mainPage)
    this.currentUrl.searchParams.set("page", page)

    window.history.pushState({}, "", this.currentUrl);
  }
  
  setPageUrl(page) {
    this.currentUrl.searchParams.set("page", page)
    window.history.pushState({}, "", this.currentUrl);
    
  }
}
