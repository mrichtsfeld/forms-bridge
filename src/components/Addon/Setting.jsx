import useApi from "../../hooks/useApi";
import Bridges from "../Bridges";
import Jobs from "../Jobs";

const { PanelRow, __experimentalSpacer: Spacer } = wp.components;

export default function AddonSetting({ children }) {
  const [api] = useApi();

  return (
    <>
      <p style={{ marginTop: 0 }}>{description}</p>
      <PanelRow>
        <Bridges />
      </PanelRow>
      {children}
      {Array.isArray(api.credentials) && (
        <>
          <Spacer paddingY="calc(8px)" />
          <Credentials />
        </>
      )}
      <Spacer paddingY="calc(8px)" />
      <Jobs />
    </>
  );
}
