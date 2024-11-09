// vendor
import React from "react";
import { __ } from "@wordpress/i18n";
import { Button, Modal } from "@wordpress/components";
import { useState } from "@wordpress/element";

// source
import PipesTable from "./Table";

export default function FormPipes({ formId, pipes, setPipes }) {
  const [open, setOpen] = useState(false);

  return (
    <>
      <Button
        variant="secondary"
        onClick={() => setOpen(true)}
        style={{ width: "130px", justifyContent: "center", height: "32px" }}
      >
        {__("Pipes", "wpct-erp-forms")}
      </Button>
      {open && (
        <Modal
          title={__("Form pipes", "wpct-erp-forms")}
          onRequestClose={() => setOpen(false)}
        >
          <PipesTable formId={formId} pipes={pipes} setPipes={setPipes} />
        </Modal>
      )}
    </>
  );
}
