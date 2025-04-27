// source
import { useGeneral } from "../../providers/Settings";
import Backends from "../../components/Backends";
import Backend from "../../components/Backends/Backend";
import Integrations from "../../components/Integrations";
import Addons from "../../components/Addons";
import Logger from "../../components/Logger";
import Exporter from "../../components/Exporter";

const {
  PanelBody,
  PanelRow,
  TextControl,
  __experimentalSpacer: Spacer,
} = wp.components;
const { useEffect } = wp.element;
const { __ } = wp.i18n;

export default function GeneralSettings() {
  const [{ notification_receiver, backends, debug, ...general }, save] =
    useGeneral();

  const update = (field) =>
    save({ notification_receiver, backends, debug, ...general, ...field });

  useEffect(() => {
    const img = document.querySelector("#general .addon-logo");
    if (!img) return;
    img.removeAttribute("src");
  }, []);

  return (
    <>
      <PanelRow>
        <TextControl
          label={__("Error notification receiver", "forms-bridge")}
          help={__(
            "Email address where submission errors will be sent with the error log and the submission data",
            "forms-bridge"
          )}
          onChange={(notification_receiver) =>
            update({ notification_receiver })
          }
          value={notification_receiver || ""}
          style={{ width: "300px" }}
          __nextHasNoMarginBottom
          __next40pxDefaultSize
        />
      </PanelRow>
      <Spacer paddingY="calc(8px)" />
      <PanelBody
        title={__("Backends", "forms-bridge")}
        initialOpen={backends.length === 0}
      >
        <PanelRow>
          <Backends
            backends={backends}
            setBackends={(backends) => update({ backends })}
            Backend={Backend}
          />
        </PanelRow>
      </PanelBody>
      <Integrations />
      <Addons />
      <Logger />
      <PanelBody
        title={__("Import / Export", "forms-bridge")}
        initialOpen={false}
      >
        <Exporter />
      </PanelBody>
      <PanelBody title={__("Credits", "forms-bridge")} initialOpen={false}>
        <ul>
          <li>
            🏠{" "}
            <a href="https://formsbridge.codeccoop.org" target="_blank">
              {__("Website", "forms-bridge")}
            </a>
          </li>
          <li>
            📔{" "}
            <a
              href="https://formsbridge.codeccoop.org/documentation/"
              target="_blank"
            >
              {__("Documentation", "forms-bridge")}
            </a>
          </li>
          <li>
            💬{" "}
            <a
              href="https://wordpress.org/support/plugin/forms-bridge/"
              target="_blank"
            >
              {__("Support", "forms-bridge")}
            </a>
          </li>
          <li>
            💵{" "}
            <a href="https://buymeacoffee.com/codeccoop" target="_blank">
              {__("Donate", "forms-bridge")}
            </a>
          </li>
        </ul>
        <p>
          <strong>Forms Bridge</strong> has been created by{" "}
          <a href="https://www.codeccoop.org" target="_blank">
            Còdec
          </a>
          , a cooperative web development studio based on Barcelona.
        </p>
        <p>
          Please rate our plugin on{" "}
          <a
            href="https://wordpress.org/support/plugin/forms-bridge/reviews/?new-post"
            target="_blank"
          >
            WordPress.org
          </a>{" "}
          and help us to maintain this plugin alive 💖
        </p>
      </PanelBody>
    </>
  );
}
