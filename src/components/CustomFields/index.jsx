// source
import CustomFieldsTable from "./Table";

const { Button, Modal } = wp.components;
const { useState, useMemo, useEffect, useRef } = wp.element;
const { __ } = wp.i18n;

const CSS = `.components-modal__frame.no-scrollable .components-modal__content {
  overflow: hidden;
}
.components-modal__frame.no-scrollable .components-modal__content > .components-modal__header + div {
  height: 100%;
}`;

export default function CustomFields({ customFields, setCustomFields }) {
  const [open, setOpen] = useState(false);

  const handleSetCustomFields = (customFields) => {
    customFields.forEach((constant) => {
      delete constant.index;
    });

    setCustomFields(customFields);
  };

  const style = useRef(document.createElement("style"));
  useEffect(() => {
    style.current.appendChild(document.createTextNode(CSS));
    document.head.appendChild(style.current);

    return () => {
      document.head.removeChild(style.current);
    };
  }, []);

  return (
    <>
      <Button
        variant={
          customFields.filter((m) => m.name).length ? "primary" : "secondary"
        }
        onClick={() => setOpen(true)}
        style={{ width: "150px", justifyContent: "center" }}
        __next40pxDefaultSize
      >
        {__("Custom fields", "forms-bridge")}
      </Button>
      {open && (
        <Modal
          title={__("Custom fields", "forms-bridge")}
          onRequestClose={() => setOpen(false)}
          className="no-scrollable"
        >
          <p
            style={{
              marginTop: "-3rem",
              position: "absolute",
              zIndex: 1,
            }}
          >
            {__(
              "Add custom fields to the form submission payload",
              "forms-bridge"
            )}
          </p>
          <div
            style={{
              marginTop: "2rem",
              width: "760px",
              maxWidth: "80vw",
              minHeight: "125px",
              height: "calc(100% - 2rem)",
              display: "flex",
              flexDirection: "column",
              borderTop: "1px solid",
              borderBottom: "1px solid",
            }}
          >
            <CustomFieldsTable
              customFields={customFields.map((constant, index) => ({
                ...constant,
                index,
              }))}
              setCustomFields={handleSetCustomFields}
            />
          </div>
        </Modal>
      )}
    </>
  );
}
