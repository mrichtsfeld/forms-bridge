// source
import Bridges from "../../../../src/components/Bridges";
import RestBridge from "./Bridge";
import useZohoApi from "../hooks/useZohoApi";

const { PanelRow } = wp.components;

export default function RestSetting() {
  const [{ bridges, templates }, save] = useZohoApi();

  const update = (field) => save({ bridges, templates, ...field });

  return (
    <PanelRow>
      <Bridges
        bridges={bridges}
        setBridges={(bridges) => update({ bridges })}
        Bridge={RestBridge}
      />
    </PanelRow>
  );
}
