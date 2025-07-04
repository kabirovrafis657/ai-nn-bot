import cv2

# Load the image and watermark mask
image = cv2.imread("naked/8737140111734392347.png")  # Image with watermark
mask = cv2.imread("watermark_mask.png", 0)  # Black-and-white mask of the watermark

# Check the dimensions
print(f"Image shape: {image.shape}")
print(f"Mask shape: {mask.shape}")

# Resize the mask if dimensions do not match
if image.shape[:2] != mask.shape[:2]:  # Compare (height, width)
    print("Resizing mask to match image dimensions...")
    mask = cv2.resize(mask, (image.shape[1], image.shape[0]), interpolation=cv2.INTER_LINEAR)  # Use INTER_NEAREST for masks

# --- Inpainting Parameters (Experiment with these) ---
inpaint_radius = 10  # Adjust this value
inpaint_method = cv2.INPAINT_NS  # Try cv2.INPAINT_NS

# Perform inpainting
output = cv2.inpaint(image, mask, inpaint_radius, inpaint_method)

# --- Optional Post-processing (Experiment with these) ---
# Apply a slight blur to the inpainted area (you'll need to create a mask for the inpainted region)
# blurred_output = cv2.GaussianBlur(output, (5, 5), 0)

# Save the result
cv2.imwrite("output_improved3412412.jpg", output)
print("Inpainting completed and saved as 'output_improved.jpg'")