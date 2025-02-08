// source
import SettingsProvider from "../../../../src/providers/Settings";
import FormsProvider from "../../../../src/providers/Forms";
import Setting from "../Setting";

// assets
import logo from "../../assets/logo.png";

const { useEffect, useState, useRef, createPortal } = wp.element;

export default function Addon() {
  const [root, setRoot] = useState(null);

  const onShowTab = useRef((setting) => {
    if (setting === "financoop") {
      setRoot(document.getElementById(setting).querySelector(".root"));
    } else {
      setRoot(null);
    }
  }).current;

  useEffect(() => {
    wpfb.on("tab", onShowTab);
  }, []);

  useEffect(() => {
    if (!root) return;
    const img = document.querySelector("#financoop .addon-logo");
    if (!img) return;
    img.setAttribute("src", "data:image/png;base64," + logo);
    img.style.width = "70px";
  }, [root]);

  return (
    <SettingsProvider handle={["financoop"]}>
      <FormsProvider>
        <div>{root && createPortal(<Setting />, root)}</div>
      </FormsProvider>
    </SettingsProvider>
  );
}
