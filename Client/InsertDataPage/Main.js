import InsertDataModel from "./InsertDataModel.js";
import InsertDataView from "./InsertDataView.js";
import InsertDataController from "./InsertDataController.js";

console.log("test");

document.addEventListener("DOMContentLoaded", () => {
  const model = new InsertDataModel();
  const view = new InsertDataView(model);
  new InsertDataController(view, model);
});

