import InsertDataModel from "./InsertDataModel.js"
import InsertDataView from "./InsertDataView.js"
import InsertDataController from "./InsertDataController.js"

console.log("test")

const model = new InsertDataModel()
const view = new InsertDataView(model)
new InsertDataController(view, model)

// const mainContainer = document.getElementById("main-container")
// const selectPageButton = document.getElementById("select-page-button")



// const handleSelectButtonClicked = (event) => {
//     console.log(event.target.value)
// }

// const setupButtonEvents = () => {
//     console.log('test')
//     selectPageButton.addEventListener("change", (event) => {
        
//         handleSelectButtonClicked(event)
//     })
// }

// const init = () => {
//     setupButtonEvents()
// }

// init()
