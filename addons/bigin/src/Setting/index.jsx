// source
import Bridges from "../../../../src/components/Bridges";
import BiginBridge from "./Bridge";
import useBiginApi from "../hooks/useBiginApi";
import Credentials from "../components/Credentials";

const { PanelBody, PanelRow, __experimentalSpacer: Spacer } = wp.components;
const { __ } = wp.i18n;

export default function BiginSetting() {
  const [{ bridges, templates, credentials, workflow_jobs }, save] =
    useBiginApi();

  const update = (field) =>
    save({ bridges, templates, credentials, workflow_jobs, ...field });

  return (
    <>
      <p style={{ marginTop: 0 }}>
        {__(
          "Use WordPress as the frontend of your CRM and don't let any lead slip through your fingers",
          "forms-bridge"
        )}
      </p>
      <PanelRow>
        <Bridges
          credentials={credentials}
          bridges={bridges}
          setBridges={(bridges) => update({ bridges })}
          Bridge={BiginBridge}
        />
      </PanelRow>
      <Spacer paddingY="calc(8px)" />
      <PanelBody
        title={__("Credentials", "forms-bridge")}
        initialOpen={credentials.length === 0}
      >
        <Credentials
          credentials={credentials}
          setCredentials={(credentials) => update({ credentials })}
        />
      </PanelBody>
    </>
  );
}
