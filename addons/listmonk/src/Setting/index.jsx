// source
import Bridges from "../../../../src/components/Bridges";
import ListmonkBridge from "./Bridge";
import useListmonkApi from "../hooks/useListmonkApi";

const { PanelRow } = wp.components;
const { __ } = wp.i18n;

export default function ListmonkSetting() {
  const [{ bridges, templates, workflow_jobs }, save] = useListmonkApi();

  const update = (field) =>
    save({ bridges, templates, workflow_jobs, ...field });

  return (
    <>
      <p style={{ marginTop: 0 }}>
        {__(
          "Listmonk is a self-hosted newsletter and mailing list manager, free and open source",
          "forms-bridge"
        )}
      </p>
      <PanelRow>
        <Bridges
          bridges={bridges}
          setBridges={(bridges) => update({ bridges })}
          Bridge={ListmonkBridge}
        />
      </PanelRow>
    </>
  );
}
