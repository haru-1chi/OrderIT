export default class InsertDataController {
    constructor(view, model) {
        this.view = view
        this.model = model
        console.log("init controller")

        this.initMainPage = this.model.getMainPageGETData() || "1";
        this.initPage = this.model.getPageGETData() || "1";

        this.currentMainPage = this.initMainPage
        this.currentPage = this.initPage
        this.#setupEvents()
        this.#setupPages()
    }

    #setupPages() {
        this.view.loadMainPage(this.initMainPage, this.initPage)
    }
    
    #setupEvents() {
        this.view.bindSelectPageButtonClicked((event) => {
            const pageId = event.target.value
            
            this.currentPage = pageId
            this.view.loadPage(pageId)
            this.model.setPageUrl(pageId)
            console.log(pageId)
        }) 

        this.view.bindSelectMainPageButtonClicked((event) => {
            const mainPageId = event.target.dataset.selectmainpage

            this.currentMainPage = mainPageId
            this.currentPage = "1"

            this.view.loadMainPage(mainPageId, "1")
            console.log(mainPageId)
            this.model.setMainPageUrl(mainPageId, "1")
        })

        this.view.bindFormSubmit((form, submitButton, event) => {
            event.preventDefault()
            
            console.log(this.model)
            form.action = `system/insert.php?mainPage=${this.currentMainPage}&page=${this.currentPage}`
            this.model.submitForm(form, submitButton)
        })

        this.view.bindUpdateFormSubmit((form, submitButton, event) => {
            event.preventDefault()
            console.log("Test")
            
            form.action = `system/update.php?mainPage=${this.currentMainPage}&page=${this.currentPage}`
            this.model.submitForm(form, submitButton)
        })
    }
}