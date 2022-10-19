import React from "react";
import ReactDOM from "react-dom";
import { BrowserRouter } from 'react-router-dom';
import App from './pages/App';
import { DAppProvider } from "@usedapp/core";

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.min.js'
import './assets/scss/global.scss'

ReactDOM.render(
  <React.StrictMode>
    <DAppProvider config={{}}>
      <BrowserRouter>
        <App />
      </BrowserRouter>
    </DAppProvider>
  </React.StrictMode>,
  document.getElementById("root")
);