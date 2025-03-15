// source
import Bridges from "../../../../src/components/Bridges";
import DolibarrBridge from "./Bridge";
import useDolibarrApi from "../hooks/useDolibarrApi";

const { PanelRow } = wp.components;

export default function DolibarrSetting() {
  const [{ bridges, templates }, save] = useDolibarrApi();

  const update = (field) => save({ bridges, templates, ...field });

  return (
    <PanelRow>
      <Bridges
        bridges={bridges}
        setBridges={(bridges) => update({ bridges })}
        Bridge={DolibarrBridge}
      />
    </PanelRow>
  );
}
