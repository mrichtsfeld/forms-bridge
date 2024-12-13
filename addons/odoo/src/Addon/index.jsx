// vendor
import React from "react";
import { useEffect, useState, useRef, createPortal } from "@wordpress/element";

// source
import SettingsProvider from "../../../../src/providers/Settings";
import FormsProvider from "../../../../src/providers/Forms";
import Setting from "../Setting";

export default function Addon() {
  const [root, setRoot] = useState(null);

  const onShowTab = useRef((setting) => {
    if (setting === "odoo-api") {
      setRoot(document.getElementById(setting));
    } else {
      setRoot(null);
    }
  }).current;

  useEffect(() => {
    wpfb.on("tab", onShowTab);
  }, []);

  return (
    <SettingsProvider handle={["odoo-api"]}>
      <FormsProvider>
        <div>{root && createPortal(<Setting />, root)}</div>
      </FormsProvider>
    </SettingsProvider>
  );
}
