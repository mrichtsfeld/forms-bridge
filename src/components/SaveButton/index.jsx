import { useLoading } from "../../providers/Loading";
import { useError } from "../../providers/Error";
import useFlushStore from "../../hooks/useFlushStore";

const { Button } = wp.components;
const { __ } = wp.i18n;

export default function SaveButton() {
  const [loading] = useLoading();
  const [error] = useError();
  const flushStore = useFlushStore();

  return (
    <div style={{ textAlign: "right" }}>
      <Button
        variant="primary"
        onClick={flushStore}
        style={{
          minWidth: "100px",
          justifyContent: "center",
          marginLeft: "auto",
        }}
        disabled={loading || error}
        __next40pxDefaultSize
      >
        {__("Save", "forms-bridge")}
      </Button>
    </div>
  );
}
