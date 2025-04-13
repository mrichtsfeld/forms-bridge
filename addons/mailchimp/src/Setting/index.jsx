// source
import Bridges from "../../../../src/components/Bridges";
import MailchimpBridge from "./Bridge";
import useMailchimpApi from "../hooks/useMailchimpApi";

const { PanelRow } = wp.components;
const { __ } = wp.i18n;

export default function MailchimpSetting() {
  const [{ bridges, templates, workflow_jobs }, save] = useMailchimpApi();

  const update = (field) =>
    save({ bridges, templates, workflow_jobs, ...field });

  return (
    <>
      <p style={{ marginTop: 0 }}>
        {__("Create opt in forms to your MailChimp audiences", "forms-bridge")}
      </p>
      <PanelRow>
        <Bridges
          bridges={bridges}
          setBridges={(bridges) => update({ bridges })}
          Bridge={MailchimpBridge}
        />
      </PanelRow>
    </>
  );
}
