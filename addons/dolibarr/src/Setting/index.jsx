// source
import Bridges from "../../../../src/components/Bridges";
import WorkflowJobs from "../../../../src/components/WorkflowJobs";
import DolibarrBridge from "./Bridge";
import useDolibarrApi from "../hooks/useDolibarrApi";

const { PanelRow, __experimentalSpacer: Spacer } = wp.components;
const { __ } = wp.i18n;

export default function DolibarrSetting() {
  const [{ bridges, templates, workflow_jobs }, save] = useDolibarrApi();

  const update = (field) =>
    save({ bridges, templates, workflow_jobs, ...field });

  return (
    <>
      <p style={{ marginTop: 0 }}>
        {__(
          "Bridge your forms to Dolibarr and convert user responses to registries on your ERP",
          "forms-bridge"
        )}
      </p>
      <PanelRow>
        <Bridges
          bridges={bridges}
          setBridges={(bridges) => update({ bridges })}
          Bridge={DolibarrBridge}
        />
      </PanelRow>
      <Spacer paddingY="calc(8px)" />
      <WorkflowJobs api="dolibarr" jobs={workflow_jobs} />
    </>
  );
}
